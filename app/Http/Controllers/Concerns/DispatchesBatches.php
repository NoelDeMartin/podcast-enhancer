<?php

namespace App\Http\Controllers\Concerns;

use App\Jobs\PrepareTranscriptionJob;
use App\Jobs\ProduceEntryJob;
use App\Jobs\StitchTranscriptionsJob;
use App\Models\Entry;
use App\Models\EntryJobBatch;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

trait DispatchesBatches
{
    protected function dispatchTranscriptionBatch(Entry $entry): void
    {
        $entryId = $entry->id;

        if (! $entryId) {
            throw new \Exception('Entry ID is missing before dispatching batch');
        }

        $batch = Bus::batch([
            new PrepareTranscriptionJob($entry),
        ])
            ->then(function (Batch $batch) use ($entryId) {
                /** @var Entry|null $entry */
                $entry = Entry::find($entryId);

                if ($entry) {
                    $productionBatch = $this->dispatchProductionBatch($entry, $batch->id);

                    Cache::put(
                        $this->transcriptionTmpCleanupDeferralCacheKey($batch->id),
                        $productionBatch->id,
                        now()->addHours(6),
                    );
                }
            })
            ->finally(function (Batch $batch) {
                $this->cleanupTranscriptionTmpDirectoryUnlessDeferred($batch->id);
            })
            ->name('Process entry '.$entryId)
            ->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entryId,
            'batch_id' => $batch->id,
        ]);
    }

    protected function dispatchProductionBatch(Entry $entry, string $transcriptionBatchId): Batch
    {
        $batch = Bus::batch([
            [
                new StitchTranscriptionsJob($entry, $transcriptionBatchId),
                new ProduceEntryJob($entry),
            ],
        ])
            ->finally(function () use ($transcriptionBatchId) {
                Storage::deleteDirectory($this->transcriptionTmpDirectory($transcriptionBatchId));
            })
            ->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entry->id,
            'batch_id' => $batch->id,
        ]);

        return $batch;
    }

    protected function dispatchMetadataBatch(Entry $entry): void
    {
        $batch = Bus::batch([
            [
                new ProduceEntryJob($entry),
            ],
        ])->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entry->id,
            'batch_id' => $batch->id,
        ]);
    }

    protected function transcriptionTmpDirectory(string $transcriptionBatchId): string
    {
        return "tmp/batch-{$transcriptionBatchId}/";
    }

    protected function transcriptionTmpCleanupDeferralCacheKey(string $transcriptionBatchId): string
    {
        return "transcription_tmp_cleanup_defer:{$transcriptionBatchId}";
    }

    protected function cleanupTranscriptionTmpDirectoryUnlessDeferred(string $transcriptionBatchId): void
    {
        $cacheKey = $this->transcriptionTmpCleanupDeferralCacheKey($transcriptionBatchId);
        $productionBatchId = Cache::pull($cacheKey);

        if ($productionBatchId) {
            return;
        }

        Storage::deleteDirectory($this->transcriptionTmpDirectory($transcriptionBatchId));
    }
}
