<?php

namespace App\Jobs;

use App\Ai\Agents\PodcastEditorAgent;
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
class ProduceEntryJob implements ShouldQueue
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

        if (! $this->entry->transcription_path) {
            Log::info(static::class.' skipped (no transcription_path)', [
                'entry_id' => $this->entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info(static::class.' started', [
            'entry_id' => $this->entry->id,
            'transcription_path' => $this->entry->transcription_path,
            'batch_id' => $this->batchId,
        ]);

        $segments = json_decode(Storage::get($this->entry->transcription_path), true);

        $transcription = collect($segments)
            ->map(fn (array $segment) => "[{$segment['start_seconds']}] {$segment['text']}")
            ->implode("\n");

        $response = (new PodcastEditorAgent)->prompt($transcription, timeout: 300);

        $this->entry->update([
            'summary' => $response['summary'],
            'chapters' => $response['chapters'],
        ]);

        Log::info(static::class.' finished (updated entry)', [
            'entry_id' => $this->entry->id,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entry->id,
            'transcription_path' => $this->entry->transcription_path,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }
}
