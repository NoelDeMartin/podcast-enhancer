<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        if ($this->entry->transcription_path) {
            Storage::delete($this->entry->transcription_path);
        }

        $path = 'transcriptions/'.Str::random(40).'.txt';
        Storage::put($path, (string) $transcript);

        $this->entry->update(['transcription_path' => $path]);
    }
}
