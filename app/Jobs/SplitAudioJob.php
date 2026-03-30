<?php

namespace App\Jobs;

use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

#[Timeout(600)]
#[Tries(1)]
class SplitAudioJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public Entry $entry, public string $audioPath) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (! Storage::exists($this->audioPath)) {
            return;
        }

        $tmpDir = "tmp/batch-{$this->batch()->id}/";
        $extension = pathinfo($this->audioPath, PATHINFO_EXTENSION);
        $media = FFMpeg::fromDisk(config('filesystems.default'))->open($this->audioPath);
        $durationInSeconds = $media->getDurationInSeconds();
        $chunkDuration = 1800; // 30 minutes
        $overlap = 30;

        $chunksCount = (int) ceil($durationInSeconds / $chunkDuration);

        $jobs = [];
        for ($i = 0; $i < $chunksCount; $i++) {
            $startTime = $i * $chunkDuration;
            $chunkFile = "{$tmpDir}/chunks/chunk_{$i}.{$extension}";

            $media->export()
                ->addFilter(['-ss', (string) $startTime, '-t', (string) ($chunkDuration + $overlap)])
                ->toDisk(config('filesystems.default'))
                ->save($chunkFile);

            $jobs[] = new TranscribeAudioJob($this->entry, $chunkFile, $i, (int) $startTime);
        }

        if (count($jobs) > 0) {
            $this->batch()->add($jobs);
        }
    }
}
