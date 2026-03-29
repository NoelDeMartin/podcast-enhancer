<?php

use App\Ai\Agents\GenerateEntryChaptersAgent;
use App\Jobs\GenerateEntryChaptersJob;
use App\Models\Entry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('generates chapters and saves the result', function () {
    Storage::fake('local');
    Storage::put('transcriptions/fake.txt', 'This is a long transcript.');

    GenerateEntryChaptersAgent::fake([
        [
            'chapters' => [
                ['title' => 'Intro', 'description' => 'Introduction to the podcast'],
                ['title' => 'Main Topic', 'description' => 'Discussing the main topic'],
            ],
        ],
    ]);

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.txt',
    ]);

    (new GenerateEntryChaptersJob($entry))->handle();

    expect($entry->fresh()->chapters)->toBeArray()
        ->and($entry->fresh()->chapters[0]['title'])->toBe('Intro')
        ->and($entry->fresh()->chapters[1]['title'])->toBe('Main Topic');

    GenerateEntryChaptersAgent::assertPrompted('Generate chapters for this transcript: This is a long transcript.');
});

it('does nothing when the entry has no transcription', function () {
    GenerateEntryChaptersAgent::fake()->preventStrayPrompts();

    $entry = Entry::factory()->create([
        'transcription_path' => null,
    ]);

    (new GenerateEntryChaptersJob($entry))->handle();

    expect($entry->fresh()->chapters)->toBeNull();
    GenerateEntryChaptersAgent::assertNeverPrompted();
});

it('does nothing when the batch has been cancelled', function () {
    GenerateEntryChaptersAgent::fake()->preventStrayPrompts();

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.txt',
    ]);

    $batch = Bus::batch([])->dispatch();
    $batch->cancel();

    $job = new GenerateEntryChaptersJob($entry);
    $job->batchId = $batch->id;
    $job->handle();

    expect($entry->fresh()->chapters)->toBeNull();
    GenerateEntryChaptersAgent::assertNeverPrompted();
});
