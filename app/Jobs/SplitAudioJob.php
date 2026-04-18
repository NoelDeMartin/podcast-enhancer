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
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

#[Timeout(300)]
#[Tries(1)]
class SplitAudioJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Entry $entry,
        public string $audioPath,
        public int $chunkIndex,
        public int $startTime,
        public int $chunkDuration,
        public int $overlap,
        public int $chunksCount,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $this->entry->id,
                'audio_path' => $this->audioPath,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        if (! Storage::exists($this->audioPath)) {
            Log::info(static::class.' skipped (audio missing)', [
                'entry_id' => $this->entry->id,
                'audio_path' => $this->audioPath,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info(static::class." started (chunk #{$this->chunkIndex})", [
            'entry_id' => $this->entry->id,
            'audio_path' => $this->audioPath,
            'chunk_index' => $this->chunkIndex,
            'start_seconds' => $this->startTime,
            'batch_id' => $this->batchId,
        ]);

        $extension = pathinfo($this->audioPath, PATHINFO_EXTENSION);
        $chunkFile = "tmp/batch-{$this->batchId}/chunks/chunk_{$this->chunkIndex}.{$extension}";

        Log::info(static::class.' exporting chunk', [
            'entry_id' => $this->entry->id,
            'audio_path' => $this->audioPath,
            'chunk_index' => $this->chunkIndex,
            'start_seconds' => $this->startTime,
            'chunk_file' => $chunkFile,
            'batch_id' => $this->batchId,
        ]);

        $media = FFMpeg::fromDisk(config('filesystems.default'))->open($this->audioPath);

        $media->export()
            ->addFilter(['-ss', (string) $this->startTime, '-t', (string) ($this->chunkDuration + $this->overlap)])
            ->toDisk(config('filesystems.default'))
            ->save($chunkFile);

        if ($batch = $this->batch()) {
            $batch->add(new TranscribeAudioJob(
                $this->entry,
                $chunkFile,
                $this->chunkIndex,
                (int) $this->startTime
            ));

            if ($this->chunkIndex + 1 < $this->chunksCount) {
                $batch->add(new SplitAudioJob(
                    $this->entry,
                    $this->audioPath,
                    $this->chunkIndex + 1,
                    ($this->chunkIndex + 1) * $this->chunkDuration,
                    $this->chunkDuration,
                    $this->overlap,
                    $this->chunksCount
                ));
            } else {
                Storage::delete($this->audioPath);
                Log::info(static::class.' deleted original audio after all splits', [
                    'audio_path' => $this->audioPath,
                    'batch_id' => $this->batchId,
                ]);
            }
        }

        Log::info(static::class." finished (queued TranscribeAudioJob chunk #{$this->chunkIndex})", [
            'entry_id' => $this->entry->id,
            'audio_path' => $this->audioPath,
            'chunk_index' => $this->chunkIndex,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entry->id,
            'audio_path' => $this->audioPath,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }
}
