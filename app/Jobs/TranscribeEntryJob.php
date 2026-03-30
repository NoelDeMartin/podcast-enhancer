<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

#[Timeout(600)]
#[Tries(1)]
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

        if (! filter_var($this->entry->audio_url, FILTER_VALIDATE_URL)) {
            $this->transcribe($this->entry->audio_url);

            return;
        }

        $audioPath = parse_url($this->entry->audio_url, PHP_URL_PATH);
        $extension = pathinfo($audioPath, PATHINFO_EXTENSION);
        $tmpFilename = 'tmp_audio_'.$this->entry->id.'.'.$extension;

        try {
            $response = Http::timeout(300)->get($this->entry->audio_url);
            Storage::disk('local')->writeStream($tmpFilename, $response->resource());

            $this->transcribe($tmpFilename, 'local');
        } finally {
            Storage::disk('local')->delete($tmpFilename);
        }
    }

    private function transcribe(string $storagePath, ?string $disk = null): void
    {
        $transcript = Transcription::fromStorage($storagePath, $disk)->diarize()->timeout(300)->generate();
        $entryId = $this->entry->id;
        $path = "transcriptions/{$entryId}.json";
        Storage::put($path, json_encode($transcript->segments->toArray()));

        $this->entry->update(['transcription_path' => $path]);
    }
}
