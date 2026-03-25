<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can store an entry with an uploaded file', function () {
    Storage::fake('local');
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
});

it('can update an entry and replace the file', function () {
    Storage::fake('local');
    $feed = Feed::factory()->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'file_path' => $oldPath,
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

    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($entry->file_path);
});

it('can delete a file when updating an entry', function () {
    Storage::fake('local');
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
