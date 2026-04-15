<?php

use App\Models\Entry;
use App\Models\EntryJobBatch;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
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
            'published_at' => now()->format('Y-m-d\TH:i'),
            'file' => $file,
        ]);

    $response->assertRedirect();

    $entry = Entry::where('name', 'New Entry')->first();
    expect($entry)->not->toBeNull();
    expect($entry->audio_url)->not->toBeNull();
    expect($entry->published_at)->not->toBeNull();

    Storage::disk('local')->assertExists($entry->audio_url);
    Bus::assertBatched(fn ($batch) => $batch->name === 'Process entry '.$entry->id);
});

it('can store an entry with an external audio URL', function () {
    Bus::fake();
    $feed = Feed::factory()->create();

    $response = $this->actingAs($this->user)
        ->post(route('entries.store'), [
            'feed_id' => $feed->id,
            'name' => 'External Entry',
            'published_at' => now()->format('Y-m-d\TH:i'),
            'audio_url' => 'https://example.com/audio.mp3',
        ]);

    $response->assertRedirect();

    $entry = Entry::where('name', 'External Entry')->first();
    expect($entry)->not->toBeNull();
    expect($entry->audio_url)->toBe('https://example.com/audio.mp3');
    expect($entry->published_at)->not->toBeNull();

    Bus::assertBatched(fn ($batch) => $batch->name === 'Process entry '.$entry->id);
});

it('triggers transcription when a new audio_url is provided', function () {
    Bus::fake();
    $feed = Feed::factory()->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/old.mp3',
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', $entry), [
            'name' => 'Updated Entry',
            'audio_url' => 'https://example.com/new.mp3',
        ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->audio_url)->toBe('https://example.com/new.mp3');
    Bus::assertBatched(fn ($batch) => $batch->name === 'Process entry '.$entry->id);
});

it('does not dispatch a transcription batch when storing entry without a file', function () {
    Bus::fake();
    $feed = Feed::factory()->create();

    $this->actingAs($this->user)
        ->post(route('entries.store'), [
            'feed_id' => $feed->id,
            'name' => 'No File Entry',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ]);

    $entry = Entry::where('name', 'No File Entry')->first();
    expect($entry)->not->toBeNull();
    expect($entry->published_at)->not->toBeNull();

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
        'audio_url' => $oldPath,
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
    expect($entry->audio_url)->not->toBe($oldPath);
    expect($entry->audio_url)->not->toBeNull();
    expect($entry->transcription_path)->toBeNull();

    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertMissing('transcriptions/old.txt');
    Storage::disk('local')->assertExists($entry->audio_url);
    Bus::assertBatched(fn ($batch) => $batch->name === 'Process entry '.$entry->id);
});

it('clears transcription, summary, and chapters when deleting a file', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('entries', 'local');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => $oldPath,
        'transcription_path' => 'transcriptions/old.txt',
        'summary' => 'Old Summary',
        'chapters' => [['title' => 'Old Chapter', 'startTime' => 0]],
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', $entry), [
            'name' => 'Updated Entry',
            'delete_file' => '1',
        ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->audio_url)->toBeNull();
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
        'name' => 'Original Name',
        'audio_url' => $oldPath,
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', $entry), [
            'name' => 'Updated Entry',
            'delete_file' => '1',
        ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->name)->toBe('Updated Entry');
    expect($entry->audio_url)->toBeNull();

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
        'audio_url' => $path,
    ]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', $entry))
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
        'audio_url' => $path,
    ]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', $entry));
    $this->actingAs($this->user)
        ->post(route('entries.produce', $entry));

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(2);
});

it('returns 422 when regenerating transcription for entry without a file', function () {
    $entry = Entry::factory()->create(['audio_url' => null]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', $entry))
        ->assertStatus(422);

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(0);
});

it('can regenerate chapters and summary from an existing transcription', function () {
    Storage::fake('local');
    Bus::fake();
    $feed = Feed::factory()->create();

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'entries/audio.mp3',
        'transcription_path' => 'transcriptions/example.json',
    ]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', $entry), [
            'reuse_transcript' => true,
        ])
        ->assertRedirect();

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(1);
});

it('returns 422 when regenerating chapters and summary without a transcription', function () {
    $entry = Entry::factory()->create([
        'audio_url' => 'entries/audio.mp3',
        'transcription_path' => null,
    ]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', $entry), [
            'reuse_transcript' => true,
        ])
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
        'audio_url' => $path,
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('entries.destroy', $entry));

    $response->assertRedirect();

    $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    Storage::disk('local')->assertMissing($path);
});

it('can view an entry', function () {
    $feed = Feed::factory()->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('entries.show', [$feed, $entry]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Entries/Show')
            ->has('entry')
        );
});
