<?php

use App\Ai\Agents\SummarizeEntryAgent;
use App\Jobs\SummarizeEntryJob;
use App\Models\Entry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('summarizes the transcript and saves the result', function () {
    Storage::fake('local');
    Storage::put('transcriptions/fake.txt', 'This is a long transcript.');

    SummarizeEntryAgent::fake([
        'This is the generated summary.',
    ]);

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.txt',
    ]);

    (new SummarizeEntryJob($entry))->handle();

    expect($entry->fresh()->summary)->toBe('This is the generated summary.');
    SummarizeEntryAgent::assertPrompted('Please summarize the following transcript: This is a long transcript.');
});

it('does nothing when the entry has no transcription', function () {
    SummarizeEntryAgent::fake()->preventStrayPrompts();

    $entry = Entry::factory()->create([
        'transcription_path' => null,
    ]);

    (new SummarizeEntryJob($entry))->handle();

    expect($entry->fresh()->summary)->toBeNull();
    SummarizeEntryAgent::assertNeverPrompted();
});

it('does nothing when the batch has been cancelled', function () {
    SummarizeEntryAgent::fake()->preventStrayPrompts();

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.txt',
    ]);

    $batch = Bus::batch([])->dispatch();
    $batch->cancel();

    $job = new SummarizeEntryJob($entry);
    $job->batchId = $batch->id;
    $job->handle();

    expect($entry->fresh()->summary)->toBeNull();
    SummarizeEntryAgent::assertNeverPrompted();
});
