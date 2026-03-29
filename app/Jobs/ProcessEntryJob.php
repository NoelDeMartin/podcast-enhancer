<?php

namespace App\Jobs;

use App\Ai\Agents\PodcastEditorAgent;
use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessEntryJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public Entry $entry) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (! $this->entry->transcription_path) {
            return;
        }

        $segments = json_decode(Storage::get($this->entry->transcription_path), true);

        $transcription = collect($segments)
            ->map(fn (array $segment) => "[{$segment['start_seconds']}] {$segment['text']}")
            ->implode("\n");

        $response = (new PodcastEditorAgent)->prompt($transcription, timeout: 300);

        $this->entry->update([
            'summary' => $response['summary'],
            'chapters' => $response['chapters'],
        ]);
    }
}
