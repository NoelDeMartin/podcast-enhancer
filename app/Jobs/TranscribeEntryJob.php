<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\RemoteAudio;
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

        if (! $this->entry->audio_url) {
            return;
        }

        if (filter_var($this->entry->audio_url, FILTER_VALIDATE_URL)) {
            $audio = new RemoteAudio($this->entry->audio_url);
            $transcript = Transcription::of($audio)->diarize()->timeout(300)->generate();
        } else {
            $transcript = Transcription::fromStorage($this->entry->audio_url)->diarize()->timeout(300)->generate();
        }

        $entryId = $this->entry->id;
        $path = "transcriptions/{$entryId}.json";
        Storage::put($path, json_encode($transcript->segments->toArray()));

        $this->entry->update(['transcription_path' => $path]);
    }
}
