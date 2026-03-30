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

#[Timeout(600)]
#[Tries(1)]
class PrepareTranscriptionJob implements ShouldQueue
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

        $extension = pathinfo(parse_url($this->entry->audio_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp3';
        $tmpPath = "tmp/batch-{$this->batch()->id}/audio.{$extension}";

        if (filter_var($this->entry->audio_url, FILTER_VALIDATE_URL)) {
            $response = Http::timeout(300)->get($this->entry->audio_url);

            Storage::writeStream($tmpPath, $response->resource());
        } else {
            Storage::copy($this->entry->audio_url, $tmpPath);
        }

        $this->batch()->add(new SplitAudioJob($this->entry, $tmpPath));
    }
}
