<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

#[Timeout(300)]
#[Tries(8)]
#[Backoff([60, 120, 240, 480, 960, 1920, 3600])]
class TranscribeAudioJob implements ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable;

    public function __construct(
        public Entry $entry,
        public string $chunkPath,
        public int $chunkIndex,
        public int $offsetSeconds,
        public int $chunksCount
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $this->entry->id,
                'chunk_path' => $this->chunkPath,
                'chunk_index' => $this->chunkIndex,
                'offset_seconds' => $this->offsetSeconds,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        if (! Storage::exists($this->chunkPath)) {
            Log::info(static::class.' skipped (chunk missing)', [
                'entry_id' => $this->entry->id,
                'chunk_path' => $this->chunkPath,
                'chunk_index' => $this->chunkIndex,
                'offset_seconds' => $this->offsetSeconds,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        $displayIndex = $this->chunkIndex + 1;
        Log::info(static::class." started (chunk #{$displayIndex} of {$this->chunksCount})", [
            'entry_id' => $this->entry->id,
            'chunk_path' => $this->chunkPath,
            'chunk_index' => $this->chunkIndex,
            'offset_seconds' => $this->offsetSeconds,
            'batch_id' => $this->batchId,
        ]);

        $transcript = Transcription::fromStorage($this->chunkPath)
            ->diarize()
            ->timeout(300)
            ->generate();

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
            'entry_id' => $this->entry->id,
            'chunk_path' => $this->chunkPath,
            'chunk_index' => $this->chunkIndex,
            'offset_seconds' => $this->offsetSeconds,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entry->id,
            'chunk_path' => $this->chunkPath,
            'chunk_index' => $this->chunkIndex,
            'offset_seconds' => $this->offsetSeconds,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }
}
