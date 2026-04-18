<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

#[Timeout(300)]
#[Tries(1)]
class PrepareTranscriptionJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public Entry $entry) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $this->entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        if (! $this->entry->audio_url) {
            Log::info(static::class.' skipped (no audio_url)', [
                'entry_id' => $this->entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info(static::class.' started', [
            'entry_id' => $this->entry->id,
            'audio_url' => $this->entry->audio_url,
            'batch_id' => $this->batchId,
        ]);

        $extension = pathinfo(parse_url($this->entry->audio_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp3';
        $tmpPath = "tmp/batch-{$this->batchId}/audio.{$extension}";

        if ($this->entry->audio_is_external) {
            Log::info(static::class.' downloading audio to local disk', [
                'entry_id' => $this->entry->id,
                'audio_url' => $this->entry->audio_url,
                'tmp_path' => $tmpPath,
                'batch_id' => $this->batchId,
            ]);
            $response = Http::timeout(300)->get($this->entry->audio_url);

            Storage::disk('local')->writeStream($tmpPath, $response->resource());
        } else {
            Log::info(static::class.' copying audio to local disk', [
                'entry_id' => $this->entry->id,
                'audio_url' => $this->entry->audio_url,
                'tmp_path' => $tmpPath,
                'batch_id' => $this->batchId,
            ]);
            Storage::disk('local')->writeStream($tmpPath, Storage::disk('public')->readStream($this->entry->audio_url));
        }

        $media = FFMpeg::fromDisk('local')->open($tmpPath);
        $durationInSeconds = $media->getDurationInSeconds();
        $chunkDuration = 1800; // 30 minutes
        $overlap = 30;

        $chunksCount = (int) ceil($durationInSeconds / $chunkDuration);

        Log::info(static::class.' calculated chunks for transcription', [
            'entry_id' => $this->entry->id,
            'audio_path' => $tmpPath,
            'duration_seconds' => $durationInSeconds,
            'chunk_duration_seconds' => $chunkDuration,
            'overlap_seconds' => $overlap,
            'chunks_count' => $chunksCount,
            'batch_id' => $this->batchId,
        ]);

        if ($chunksCount > 0 && $batch = $this->batch()) {
            $batch->add(new SplitAudioJob(
                $this->entry,
                $tmpPath,
                0,
                0,
                $chunkDuration,
                $overlap,
                $chunksCount
            ));
        }

        Log::info(static::class.' finished (queued first SplitAudioJob chunk #1 of '.$chunksCount.')', [
            'entry_id' => $this->entry->id,
            'audio_path' => $tmpPath,
            'chunks_count' => $chunksCount,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entry->id,
            'audio_url' => $this->entry->audio_url,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }
}
