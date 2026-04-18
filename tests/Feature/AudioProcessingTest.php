<?php

use App\Jobs\PrepareTranscriptionJob;
use App\Jobs\SplitAudioJob;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Tests\TestCase;

it('dispatches the correct initial batch', function () {
    Bus::fake();

    /** @var TestCase $this */
    $user = User::factory()->create();
    $feed = Feed::factory()->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/audio.mp3',
    ]);

    $response = $this->actingAs($user)
        ->post(route('entries.produce', $entry));

    $response->assertRedirect();

    Bus::assertBatched(function ($batch) use ($entry) {
        return $batch->name === 'Process entry '.$entry->id &&
            $batch->jobs->count() === 1 &&
            $batch->jobs->first() instanceof PrepareTranscriptionJob;
    });
});

it('prepare transcription job downloads file and adds next job', function () {
    config()->set('queue.default', 'database');
    Queue::fake();
    Storage::fake('local');
    Storage::fake('public');
    Http::fake([
        'https://example.com/audio.mp3' => Http::response('fake-audio-content', 200),
    ]);

    $entry = Entry::factory()->create([
        'audio_url' => 'https://example.com/audio.mp3',
    ]);

    $batch = Bus::batch([])->dispatch();

    FFMpeg::shouldReceive('fromDisk')
        ->with('local')
        ->once()
        ->andReturnSelf();

    FFMpeg::shouldReceive('open')
        ->once()
        ->andReturnSelf();

    FFMpeg::shouldReceive('getDurationInSeconds')
        ->once()
        ->andReturn(60); // 1 minute, so a single chunk

    $job = new PrepareTranscriptionJob($entry);
    $job->withBatchId($batch->id);
    $job->handle();

    $tmpPath = "tmp/batch-{$batch->id}/audio.mp3";
    Storage::disk('local')->assertExists($tmpPath);

    Queue::assertPushed(SplitAudioJob::class, function (SplitAudioJob $job) use ($entry, $tmpPath) {
        return $job->entry->is($entry)
            && $job->audioPath === $tmpPath
            && $job->chunkIndex === 0
            && $job->startTime === 0
            && $job->chunksCount === 1;
    });
});
