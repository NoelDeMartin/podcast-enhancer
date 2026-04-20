<?php

use App\Jobs\TranscribeAudioJob;
use App\Models\Entry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Transcription;

it('postpones transcription when rate limited', function () {
    Storage::fake('local');
    Storage::put('chunks/test.wav', 'dummy content');

    Transcription::fake(function () {
        throw new RateLimitedException('Mistral rate limited', 429);
    });

    $entry = Entry::factory()->create();
    $job = new TranscribeAudioJob($entry, 'chunks/test.wav', 0, 0, 1);
    $job->withFakeQueueInteractions();

    Log::shouldReceive('info')->withArgs(fn ($message) => str_contains($message, 'started'))->once();
    Log::shouldReceive('info')->withArgs(fn ($message) => str_contains($message, 'postponed'))->once();

    $job->handle();

    $job->assertReleased(60);
});

it('throws exception when rate limited and attempts are exhausted', function () {
    Storage::fake('local');
    Storage::put('chunks/test.wav', 'dummy content');

    Transcription::fake(function () {
        throw new RateLimitedException('Mistral rate limited', 429);
    });

    $entry = Entry::factory()->create();

    // We need to simulate attempts. Laravel stores this in the job's connection if it's actually queued.
    // For manual testing we can try to set it via reflection if needed, but ShouldQueue jobs have attempts() method.

    $job = Mockery::mock(TranscribeAudioJob::class, [$entry, 'chunks/test.wav', 0, 0, 1])->makePartial();
    $job->shouldReceive('attempts')->andReturn(8);

    Log::shouldReceive('info')->withArgs(fn ($message) => str_contains($message, 'started'))->once();

    expect(fn () => $job->handle())->toThrow(RateLimitedException::class);
});
