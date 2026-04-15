<?php

use App\Concerns\DispatchesBatches;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

it('defers transcription tmp cleanup when a production batch is scheduled', function () {
    config()->set('cache.default', 'array');
    Cache::flush();

    Storage::fake();

    $dispatcher = new class
    {
        use DispatchesBatches;

        public function defer(string $transcriptionBatchId, string $productionBatchId): void
        {
            Cache::put($this->transcriptionTmpCleanupDeferralCacheKey($transcriptionBatchId), $productionBatchId, now()->addMinutes(10));
        }

        public function cleanupUnlessDeferred(string $transcriptionBatchId): void
        {
            $this->cleanupTranscriptionTmpDirectoryUnlessDeferred($transcriptionBatchId);
        }

        public function tmpDir(string $transcriptionBatchId): string
        {
            return $this->transcriptionTmpDirectory($transcriptionBatchId);
        }
    };

    $transcriptionBatchId = 't-123';
    $productionBatchId = 'p-456';

    Storage::put($dispatcher->tmpDir($transcriptionBatchId).'transcriptions/chunk_0.json', json_encode([['text' => 'hi', 'start_seconds' => 0]]));

    $dispatcher->defer($transcriptionBatchId, $productionBatchId);
    $dispatcher->cleanupUnlessDeferred($transcriptionBatchId);

    Storage::assertExists($dispatcher->tmpDir($transcriptionBatchId).'transcriptions/chunk_0.json');
});

it('cleans up transcription tmp directory when not deferred', function () {
    config()->set('cache.default', 'array');
    Cache::flush();

    Storage::fake();

    $dispatcher = new class
    {
        use DispatchesBatches;

        public function cleanupUnlessDeferred(string $transcriptionBatchId): void
        {
            $this->cleanupTranscriptionTmpDirectoryUnlessDeferred($transcriptionBatchId);
        }

        public function tmpDir(string $transcriptionBatchId): string
        {
            return $this->transcriptionTmpDirectory($transcriptionBatchId);
        }
    };

    $transcriptionBatchId = 't-999';

    Storage::put($dispatcher->tmpDir($transcriptionBatchId).'transcriptions/chunk_0.json', json_encode([['text' => 'hi', 'start_seconds' => 0]]));

    $dispatcher->cleanupUnlessDeferred($transcriptionBatchId);

    Storage::assertMissing($dispatcher->tmpDir($transcriptionBatchId).'transcriptions/chunk_0.json');
});
