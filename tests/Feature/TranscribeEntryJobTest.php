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
    $entry = Entry::factory()->create(['file_path' => $path]);

    (new TranscribeEntryJob($entry))->handle();

    expect($entry->fresh()->transcription)->toBe('This is the transcribed content.');
    Transcription::assertGenerated(fn ($prompt) => true);
});

it('does nothing when the entry has no file', function () {
    Transcription::fake()->preventStrayTranscriptions();

    $entry = Entry::factory()->create(['file_path' => null]);

    (new TranscribeEntryJob($entry))->handle();

    expect($entry->fresh()->transcription)->toBeNull();
    Transcription::assertNothingGenerated();
});

it('does nothing when the batch has been cancelled', function () {
    Transcription::fake()->preventStrayTranscriptions();

    Storage::fake('local');
    $path = UploadedFile::fake()->create('audio.mp3', 1024)->store('entries', 'local');
    $entry = Entry::factory()->create(['file_path' => $path]);

    $batch = Bus::batch([])->dispatch();
    $batch->cancel();

    $job = new TranscribeEntryJob($entry);
    $job->batchId = $batch->id;
    $job->handle();

    expect($entry->fresh()->transcription)->toBeNull();
    Transcription::assertNothingGenerated();
});
