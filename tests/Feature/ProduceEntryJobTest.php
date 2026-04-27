<?php

use App\Ai\Agents\PodcastEditorAgent;
use App\Jobs\ProduceEntryJob;
use App\Models\Entry;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\ProviderOverloadedException;

beforeEach(function () {
    Storage::fake('local');
    Config::set('ai.default', 'mistral');
    Config::set('ai.default_failover', 'anthropic');
});

it('processes the transcript and saves summary and chapters', function () {
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

    (new ProduceEntryJob($entry->id))->handle();

    $entry->refresh();

    expect($entry->summary)->toBe('This is the generated summary.')
        ->and($entry->chapters)->toBeArray()
        ->and($entry->chapters[0]['title'])->toBe('Intro')
        ->and($entry->chapters[0]['startTime'])->toBe(0)
        ->and($entry->chapters[1]['title'])->toBe('Main Topic')
        ->and($entry->chapters[1]['startTime'])->toBe(5);

    PodcastEditorAgent::assertPrompted('[0] Welcome to the show. Today we discuss AI.');
});

it('uses original_summary in the prompt if present', function () {
    $segments = [
        ['text' => 'Hello.', 'speaker' => 'Speaker 1', 'start_seconds' => 0, 'end_seconds' => 5],
    ];

    Storage::put('transcriptions/fake.json', json_encode($segments));

    PodcastEditorAgent::fake([
        [
            'summary' => 'AI Summary.',
            'chapters' => [['title' => 'Intro', 'startTime' => 0]],
        ],
    ]);

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.json',
        'original_summary' => 'Original Summary.',
    ]);

    (new ProduceEntryJob($entry->id))->handle();

    $entry->refresh();

    expect($entry->summary)->toBe('AI Summary.')
        ->and($entry->original_summary)->toBe('Original Summary.');
    PodcastEditorAgent::assertPrompted("ORIGINAL EPISODE SUMMARY:\nOriginal Summary.\n\nTRANSCRIPT:\n[0] Hello.");
});

it('does nothing when the entry has no transcription', function () {
    PodcastEditorAgent::fake()->preventStrayPrompts();

    $entry = Entry::factory()->create([
        'transcription_path' => null,
    ]);

    (new ProduceEntryJob($entry->id))->handle();

    expect($entry->fresh()->summary)->toBeNull();
    expect($entry->fresh()->chapters)->toBeNull();
    PodcastEditorAgent::assertNeverPrompted();
});

it('uses default provider on attempts 1-3', function (int $attempts) {
    $segments = [['text' => 'Hello.', 'start_seconds' => 0]];
    Storage::put('transcriptions/fake.json', json_encode($segments));

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.json',
    ]);

    PodcastEditorAgent::fake([
        [
            'summary' => 'Summary.',
            'chapters' => [['title' => 'Intro', 'startTime' => 0]],
        ],
    ]);

    $job = Mockery::mock(ProduceEntryJob::class, [$entry->id])->makePartial();
    $job->shouldReceive('attempts')->andReturn($attempts);

    $job->handle();

    PodcastEditorAgent::assertPrompted(function ($prompt) {
        return $prompt->provider->name() === 'mistral';
    });
})->with([1, 2, 3]);

it('uses failover provider array on attempt 4 and above', function (int $attempts) {
    $segments = [['text' => 'Hello.', 'start_seconds' => 0]];
    Storage::put('transcriptions/fake.json', json_encode($segments));

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.json',
    ]);

    PodcastEditorAgent::fake([
        [
            'summary' => 'Summary.',
            'chapters' => [['title' => 'Intro', 'startTime' => 0]],
        ],
    ]);

    $job = Mockery::mock(ProduceEntryJob::class, [$entry->id])->makePartial();
    $job->shouldReceive('attempts')->andReturn($attempts);

    $job->handle();

    PodcastEditorAgent::assertPrompted(fn ($prompt) => $prompt->provider->name() === 'mistral');
})->with([4, 5]);

it('fails over to anthropic on attempt 4+ if mistral fails', function () {
    $segments = [['text' => 'Hello.', 'start_seconds' => 0]];
    Storage::put('transcriptions/fake.json', json_encode($segments));

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.json',
    ]);

    PodcastEditorAgent::fake([
        fn ($prompt, $attachments, $provider) => $provider->name() === 'mistral'
            ? throw new ProviderOverloadedException('Mistral Overloaded')
            : [
                'summary' => 'Failover Summary.',
                'chapters' => [['title' => 'Intro', 'startTime' => 0]],
            ],
    ]);

    $job = Mockery::mock(ProduceEntryJob::class, [$entry->id])->makePartial();
    $job->shouldReceive('attempts')->andReturn(4);

    $job->handle();

    PodcastEditorAgent::assertPrompted(fn ($prompt) => $prompt->provider->name() === 'anthropic');
});

it('postpones job when provider is overloaded', function () {
    $segments = [['text' => 'Hello.', 'start_seconds' => 0]];
    Storage::put('transcriptions/fake.json', json_encode($segments));

    $entry = Entry::factory()->create([
        'transcription_path' => 'transcriptions/fake.json',
    ]);

    PodcastEditorAgent::fake([
        fn () => throw new ProviderOverloadedException('Overloaded'),
    ]);

    $job = Mockery::mock(ProduceEntryJob::class, [$entry->id])->makePartial();
    $job->shouldReceive('attempts')->andReturn(1);
    $job->shouldReceive('release')->once();

    $job->handle();
});
