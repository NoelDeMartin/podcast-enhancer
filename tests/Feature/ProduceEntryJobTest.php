<?php

use App\Ai\Agents\PodcastEditorAgent;
use App\Jobs\ProduceEntryJob;
use App\Models\Entry;
use Illuminate\Support\Facades\Storage;

it('processes the transcript and saves summary and chapters', function () {
    Storage::fake('local');

    $segments = [
        ['text' => 'Welcome to the show.', 'speaker' => 'Speaker 1', 'start_seconds' => 0, 'end_seconds' => 5],
        ['text' => 'Today we discuss AI.', 'speaker' => 'Speaker 1', 'start_seconds' => 5, 'end_seconds' => 10],
    ];

    Storage::put('transcriptions/fake.json', json_encode($segments));

    PodcastEditorAgent::fake([
        [
            'summary' => 'This is the generated summary.',
            'chapters' => [
                ['title' => 'Intro', 'startTime' => 0],
                ['title' => 'Main Topic', 'startTime' => 5],
            ],
        ],
    ]);

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.json',
    ]);

    (new ProduceEntryJob($entry))->handle();

    $entry->refresh();

    expect($entry->summary)->toBe('This is the generated summary.')
        ->and($entry->chapters)->toBeArray()
        ->and($entry->chapters[0]['title'])->toBe('Intro')
        ->and($entry->chapters[0]['startTime'])->toBe(0)
        ->and($entry->chapters[1]['title'])->toBe('Main Topic')
        ->and($entry->chapters[1]['startTime'])->toBe(5);

    PodcastEditorAgent::assertPrompted('[0] Welcome to the show. Today we discuss AI.');
});

it('does nothing when the entry has no transcription', function () {
    PodcastEditorAgent::fake()->preventStrayPrompts();

    $entry = Entry::factory()->create([
        'transcription_path' => null,
    ]);

    (new ProduceEntryJob($entry))->handle();

    expect($entry->fresh()->summary)->toBeNull();
    expect($entry->fresh()->chapters)->toBeNull();
    PodcastEditorAgent::assertNeverPrompted();
});
