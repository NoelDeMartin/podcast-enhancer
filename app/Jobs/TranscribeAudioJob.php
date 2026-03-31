<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

#[Timeout(300)]
#[Tries(1)]
class TranscribeAudioJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Entry $entry,
        public string $chunkPath,
        public int $chunkIndex,
        public int $offsetSeconds
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

        Log::info(static::class.' started', [
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
            "tmp/batch-{$this->batch()->id}/transcriptions/chunk_{$this->chunkIndex}.json",
            json_encode($segments->toArray())
        );

        Log::info(static::class.' finished (saved transcription chunk)', [
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
