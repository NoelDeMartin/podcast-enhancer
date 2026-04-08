<?php

namespace App\Http\Controllers\Concerns;

use App\Jobs\PrepareTranscriptionJob;
use App\Jobs\ProduceEntryJob;
use App\Jobs\StitchTranscriptionsJob;
use App\Models\Entry;
use App\Models\EntryJobBatch;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
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
                $entry = Entry::find($entryId);

                if ($entry) {
                    $this->dispatchProductionBatch($entry, $batch->id);
                }
            })
            ->finally(function (Batch $batch) {
                Storage::deleteDirectory("tmp/batch-{$batch->id}/");
            })
            ->name('Process entry '.$entryId)
            ->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entryId,
            'batch_id' => $batch->id,
        ]);
    }

    protected function dispatchProductionBatch(Entry $entry, string $transcriptionBatchId): void
    {
        $batch = Bus::batch([
            [
                new StitchTranscriptionsJob($entry, $transcriptionBatchId),
                new ProduceEntryJob($entry),
            ],
        ])->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entry->id,
            'batch_id' => $batch->id,
        ]);
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
}
