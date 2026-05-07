<?php

use App\Jobs\PrepareTranscriptionJob;
use App\Jobs\SplitAudioJob;
use App\Models\CreditUsage;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

beforeEach(function () {
    Storage::fake('local');
    Http::fake([
        'https://example.com/audio.mp3' => Http::response('fake-audio-content', 200),
    ]);
});

it('consumes credits successfully when enough funds are available', function () {
    Queue::fake();
    $user = User::factory()->create(['credits' => 10]); // 10 credits
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->for($feed)->create([
        'audio_url' => 'https://example.com/audio.mp3',
    ]);

    $batch = Bus::batch([])->dispatch();

    FFMpeg::shouldReceive('fromDisk')->andReturnSelf();
    FFMpeg::shouldReceive('open')->andReturnSelf();
    FFMpeg::shouldReceive('getDurationInSeconds')->andReturn(125); // 2 minutes and 5 seconds -> 3 credits

    $job = new PrepareTranscriptionJob($entry->id, $user->id);
    $job->withBatchId($batch->id);
    $job->handle();

    expect($entry->fresh()->duration)->toBe(125);
    expect($user->fresh()->credits)->toBe(7);
    expect(CreditUsage::count())->toBe(1);
    expect(CreditUsage::first())->toMatchArray([
        'user_id' => $user->id,
        'entry_id' => $entry->id,
        'credits' => 3,
    ]);

    Queue::assertPushed(SplitAudioJob::class);
});

it('fails and deletes temp file when insufficient credits', function () {
    Queue::fake();
    $user = User::factory()->create(['credits' => 2]); // Only 2 credits
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->for($feed)->create([
        'audio_url' => 'https://example.com/audio.mp3',
    ]);

    $batch = Bus::batch([])->dispatch();

    FFMpeg::shouldReceive('fromDisk')->andReturnSelf();
    FFMpeg::shouldReceive('open')->andReturnSelf();
    FFMpeg::shouldReceive('getDurationInSeconds')->andReturn(125); // 3 credits required

    $job = new PrepareTranscriptionJob($entry->id, $user->id);
    $job->withBatchId($batch->id);

    $tmpPath = "tmp/batch-{$batch->id}/audio.mp3";

    // We expect the handle method to throw an exception because it calls $this->fail()
    try {
        $job->handle();
    } catch (Exception $e) {
        expect($e->getMessage())->toContain('Insufficient credits');
    }

    expect($user->fresh()->credits)->toBe(2); // Credits unchanged
    expect(CreditUsage::count())->toBe(0);

    Storage::disk('local')->assertMissing($tmpPath);
    Queue::assertNotPushed(SplitAudioJob::class);
});

it('calculates credits by rounding up minutes', function ($seconds, $expectedCredits) {
    Queue::fake();
    $user = User::factory()->create(['credits' => 100]);
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->for($feed)->create([
        'audio_url' => 'https://example.com/audio.mp3',
    ]);

    $batch = Bus::batch([])->dispatch();

    FFMpeg::shouldReceive('fromDisk')->andReturnSelf();
    FFMpeg::shouldReceive('open')->andReturnSelf();
    FFMpeg::shouldReceive('getDurationInSeconds')->andReturn($seconds);

    $job = new PrepareTranscriptionJob($entry->id, $user->id);
    $job->withBatchId($batch->id);
    $job->handle();

    expect($user->fresh()->credits)->toBe(100 - $expectedCredits);
    expect(CreditUsage::first()->credits)->toBe($expectedCredits);
})->with([
    [59, 1],
    [60, 1],
    [61, 2],
    [120, 2],
    [121, 3],
]);
