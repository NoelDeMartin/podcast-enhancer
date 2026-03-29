<?php

use App\Models\Entry;
use App\Models\EntryJobBatch;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can store an entry with an uploaded file', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();

    $file = UploadedFile::fake()->create('audio.mp3', 1024);

    $response = $this->actingAs($this->user)
        ->post(route('entries.store'), [
            'feed_id' => $feed->id,
            'name' => 'New Entry',
            'description' => 'A great entry',
            'file' => $file,
        ]);

    $response->assertRedirect();

    $entry = Entry::where('name', 'New Entry')->first();
    expect($entry)->not->toBeNull();
    expect($entry->file_path)->not->toBeNull();

    Storage::disk('local')->assertExists($entry->file_path);
    Bus::assertBatched(fn ($batch) => $batch->name === 'Transcribe Entry: '.$entry->id);
});

it('does not dispatch a transcription batch when storing entry without a file', function () {
    Bus::fake();
    $feed = Feed::factory()->create();

    $this->actingAs($this->user)
        ->post(route('entries.store'), [
            'feed_id' => $feed->id,
            'name' => 'No File Entry',
        ]);

    Bus::assertNothingBatched();
});

it('can update an entry and replace the file', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $oldPath,
        'transcription_path' => 'transcriptions/old.txt',
    ]);

    $newFile = UploadedFile::fake()->create('new.mp3', 1024);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', $entry), [
            'name' => 'Updated Entry',
            'file' => $newFile,
        ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->name)->toBe('Updated Entry');
    expect($entry->file_path)->not->toBe($oldPath);
    expect($entry->file_path)->not->toBeNull();
    expect($entry->transcription_path)->toBeNull();

    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertMissing('transcriptions/old.txt');
    Storage::disk('local')->assertExists($entry->file_path);
    Bus::assertBatched(fn ($batch) => $batch->name === 'Transcribe Entry: '.$entry->id);
});

it('clears transcription, summary, and chapters when deleting a file', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $oldPath,
        'transcription_path' => 'transcriptions/old.txt',
        'summary' => 'Old Summary',
        'chapters' => ['Old Chapter'],
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', $entry), [
            'name' => 'Updated Entry',
            'delete_file' => true,
        ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->file_path)->toBeNull();
    expect($entry->transcription_path)->toBeNull();
    expect($entry->summary)->toBeNull();
    expect($entry->chapters)->toBeNull();
    Bus::assertNothingBatched();
});

it('can delete a file when updating an entry', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $oldPath,
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', $entry), [
            'name' => 'Updated Entry',
            'delete_file' => true,
        ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->name)->toBe('Updated Entry');
    expect($entry->file_path)->toBeNull();

    Storage::disk('local')->assertMissing($oldPath);
});

it('records the job batch on the entry when dispatching transcription', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);
    $path = $file->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $path,
    ]);

    $this->actingAs($this->user)
        ->post(route('entries.transcribe', $entry))
        ->assertRedirect();

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(1);
});

it('accumulates a new batch record each time transcription is triggered', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);
    $path = $file->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $path,
    ]);

    $this->actingAs($this->user)->post(route('entries.transcribe', $entry));
    $this->actingAs($this->user)->post(route('entries.transcribe', $entry));

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(2);
});

it('returns 422 when regenerating transcription for entry without a file', function () {
    $entry = Entry::factory()->create(['file_path' => null]);

    $this->actingAs($this->user)
        ->post(route('entries.transcribe', $entry))
        ->assertStatus(422);

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(0);
});

it('deletes the file when an entry is destroyed', function () {
    Storage::fake('local');
    $feed = Feed::factory()->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);
    $path = $file->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $path,
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('entries.destroy', $entry));

    $response->assertRedirect();

    $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    Storage::disk('local')->assertMissing($path);
});
