<?php

namespace App\Jobs;

use App\Concerns\HandlesAiErrors;
use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Transcription;

#[Timeout(300)]
#[Tries(8)]
class TranscribeAudioJob implements ShouldQueue
{
    use Batchable, HandlesAiErrors, InteractsWithQueue, Queueable;

    public function __construct(
        public int $entryId,
        public string $chunkPath,
        public int $chunkIndex,
        public int $offsetSeconds,
        public int $chunksCount
    ) {}

    public function handle(): void
    {
        $entry = Entry::findOrFail($this->entryId);

        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $entry->id,
                'chunk_path' => $this->chunkPath,
                'chunk_index' => $this->chunkIndex,
                'offset_seconds' => $this->offsetSeconds,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        if (! Storage::exists($this->chunkPath)) {
            Log::info(static::class.' skipped (chunk missing)', [
                'entry_id' => $entry->id,
                'chunk_path' => $this->chunkPath,
                'chunk_index' => $this->chunkIndex,
                'offset_seconds' => $this->offsetSeconds,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        $displayIndex = $this->chunkIndex + 1;
        Log::info(static::class." started (chunk #{$displayIndex} of {$this->chunksCount})", [
            'entry_id' => $entry->id,
            'chunk_path' => $this->chunkPath,
            'chunk_index' => $this->chunkIndex,
            'offset_seconds' => $this->offsetSeconds,
            'batch_id' => $this->batchId,
        ]);

        try {
            $transcript = Transcription::fromStorage($this->chunkPath)
                ->diarize()
                ->timeout(300)
                ->generate();
        } catch (RateLimitedException $e) {
            $this->postponeIfRateLimited($e, [
                'entry_id' => $entry->id,
                'chunk_path' => $this->chunkPath,
                'chunk_index' => $this->chunkIndex,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        $segments = collect($transcript->segments)->map(function ($segment) {
            $data = $segment->toArray();
            $data['start_seconds'] = (float) $data['start_seconds'] + $this->offsetSeconds;
            $data['end_seconds'] = (float) $data['end_seconds'] + $this->offsetSeconds;

            return $data;
        });

        Storage::put(
            "tmp/batch-{$this->batchId}/transcriptions/chunk_{$this->chunkIndex}.json",
            json_encode($segments->toArray())
        );

        Storage::delete($this->chunkPath);

        Log::info(static::class." finished (saved transcription chunk #{$displayIndex} of {$this->chunksCount} and deleted chunk file)", [
            'entry_id' => $entry->id,
            'chunk_path' => $this->chunkPath,
            'chunk_index' => $this->chunkIndex,
            'offset_seconds' => $this->offsetSeconds,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entryId,
            'chunk_path' => $this->chunkPath,
            'chunk_index' => $this->chunkIndex,
            'offset_seconds' => $this->offsetSeconds,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }
}
