<?php

namespace App\Jobs;

use App\Ai\Agents\GenerateEntryChaptersAgent;
use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateEntryChaptersJob implements ShouldQueue
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

        $transcription = Storage::get($this->entry->transcription_path);

        $response = (new GenerateEntryChaptersAgent)->prompt('Generate chapters for this transcript: '.$transcription);

        $this->entry->update(['chapters' => $response['chapters'] ?? []]);
    }
}
