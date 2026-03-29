<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

class TranscribeEntryJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public Entry $entry) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (! $this->entry->file_path) {
            return;
        }

        $transcript = Transcription::fromStorage($this->entry->file_path)->diarize()->generate();

        $entryId = $this->entry->id;
        $path = "transcriptions/{$entryId}.json";
        Storage::put($path, json_encode($transcript->segments->toArray()));

        $this->entry->update(['transcription_path' => $path]);
    }
}
