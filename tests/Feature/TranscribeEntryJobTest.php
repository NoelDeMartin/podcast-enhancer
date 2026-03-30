<?php

use App\Jobs\TranscribeEntryJob;
use App\Models\Entry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

it('transcribes the audio file and saves the result', function () {
    Storage::fake('local');
    Transcription::fake(['This is the transcribed content.']);

    $path = UploadedFile::fake()->create('audio.mp3', 1024)->store('entries', 'local');
    $entry = Entry::factory()->create(['audio_url' => $path]);

    (new TranscribeEntryJob($entry))->handle();

    $entry->refresh();
    expect($entry->transcription_path)->not->toBeNull();
    Storage::disk('local')->assertExists($entry->transcription_path);

    $transcription = json_decode(Storage::disk('local')->get($entry->transcription_path), true);
    expect($transcription)->toMatchArray([
        [
            'text' => 'This is the transcribed content.',
            'speaker' => 'Speaker 1',
            'start_seconds' => 0,
            'end_seconds' => 1,
        ],
    ]);
    Transcription::assertGenerated(fn ($prompt) => true);
});

it('does nothing when the entry has no file', function () {
    Transcription::fake()->preventStrayTranscriptions();

    $entry = Entry::factory()->create(['audio_url' => null]);

    (new TranscribeEntryJob($entry))->handle();

    expect($entry->fresh()->transcription_path)->toBeNull();
    Transcription::assertNothingGenerated();
});

it('does nothing when the batch has been cancelled', function () {
    Transcription::fake()->preventStrayTranscriptions();

    Storage::fake('local');
    $path = UploadedFile::fake()->create('audio.mp3', 1024)->store('entries', 'local');
    $entry = Entry::factory()->create(['audio_url' => $path]);

    $batch = Bus::batch([])->dispatch();
    $batch->cancel();

    $job = new TranscribeEntryJob($entry);
    $job->batchId = $batch->id;
    $job->handle();

    expect($entry->fresh()->transcription_path)->toBeNull();
    Transcription::assertNothingGenerated();
});
