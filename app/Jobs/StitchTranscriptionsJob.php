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

#[Timeout(300)]
#[Tries(1)]
class StitchTranscriptionsJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public int $entryId, public string $transcriptionBatchId) {}

    public function handle(): void
    {
        $entry = Entry::findOrFail($this->entryId);

        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $entry->id,
                'transcription_batch_id' => $this->transcriptionBatchId,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        $transcriptionsDir = "tmp/batch-{$this->transcriptionBatchId}/transcriptions";
        $files = Storage::files($transcriptionsDir);

        if (empty($files)) {
            Log::info(static::class.' skipped (no transcription chunks found)', [
                'entry_id' => $entry->id,
                'transcriptions_dir' => $transcriptionsDir,
                'transcription_batch_id' => $this->transcriptionBatchId,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info(static::class.' started', [
            'entry_id' => $entry->id,
            'transcriptions_dir' => $transcriptionsDir,
            'files_count' => count($files),
            'transcription_batch_id' => $this->transcriptionBatchId,
            'batch_id' => $this->batchId,
        ]);

        // Sort files by index in filename chunk_{index}.json
        $files = collect($files)->sortBy(function ($file) {
            preg_match('/chunk_(\d+)\.json/', $file, $matches);

            return (int) ($matches[1] ?? 0);
        });

        $path = "transcriptions/{$entry->id}.json";
        $chunkSize = 1800;

        $tempFile = tempnam(sys_get_temp_dir(), 'stitch');
        $fp = fopen($tempFile, 'w');
        fwrite($fp, '[');
        $first = true;

        foreach ($files as $file) {
            preg_match('/chunk_(\d+)\.json/', $file, $matches);
            $chunkIndex = (int) ($matches[1] ?? 0);
            $windowStart = $chunkIndex * $chunkSize;
            $windowEnd = ($chunkIndex + 1) * $chunkSize;

            $segments = json_decode(Storage::get($file), true);

            foreach ($segments as $segment) {
                // For all but the last chunk, only take segments starting in the window
                if ($segment['start_seconds'] >= $windowStart && $segment['start_seconds'] < $windowEnd) {
                    if (! $first) {
                        fwrite($fp, ',');
                    }
                    fwrite($fp, json_encode($segment));
                    $first = false;
                }
            }
        }
        fwrite($fp, ']');
        fclose($fp);

        Storage::put($path, fopen($tempFile, 'r'));
        unlink($tempFile);

        $entry->update(['transcription_path' => $path]);

        Log::info(static::class.' finished (saved stitched transcription)', [
            'entry_id' => $entry->id,
            'transcription_path' => $path,
            'transcription_batch_id' => $this->transcriptionBatchId,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entryId,
            'transcription_batch_id' => $this->transcriptionBatchId,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }
}
