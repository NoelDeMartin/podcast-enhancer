<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

#[Timeout(600)]
#[Tries(1)]
class TranscribeAudioJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Entry $entry,
        public string $chunkPath,
        public int $chunkIndex,
        public int $offsetSeconds
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (! Storage::exists($this->chunkPath)) {
            return;
        }

        $transcript = Transcription::fromStorage($this->chunkPath)
            ->diarize()
            ->timeout(300)
            ->generate();

        $segments = collect($transcript->segments)->map(function ($segment) {
            $data = $segment->toArray();
            $data['start_seconds'] = (float) $data['start_seconds'] + $this->offsetSeconds;
            $data['end_seconds'] = (float) $data['end_seconds'] + $this->offsetSeconds;

            return $data;
        });

        Storage::put(
            "tmp/batch-{$this->batch()->id}/transcriptions/chunk_{$this->chunkIndex}.json",
            json_encode($segments->toArray())
        );
    }
}
