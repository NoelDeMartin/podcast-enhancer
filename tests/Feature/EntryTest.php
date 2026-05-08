<?php

use App\Models\Entry;
use App\Models\EntryJobBatch;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->pro()->create();
    $this->actingAs($this->user);
});

it('can store an entry with an uploaded file', function () {
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();

    $file = UploadedFile::fake()->create('audio.mp3', 1024);

    $response = $this->post(route('entries.store', $feed), [
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

    Storage::disk('public')->assertExists($entry->audio_url);
    Bus::assertBatched(fn ($batch) => $batch->name === 'Process entry '.$entry->id);
});

it('can store an entry with an external audio URL', function () {
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();

    $response = $this->post(route('entries.store', $feed), [
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
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/old.mp3',
    ]);

    $response = $this->put(route('entries.update', [$feed, $entry]), [
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
    $feed = Feed::factory()->for($this->user)->create();

    $this->post(route('entries.store', $feed), [
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
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('audios', 'public');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => $oldPath,
        'transcription_path' => 'transcriptions/old.txt',
    ]);

    $newFile = UploadedFile::fake()->create('new.mp3', 1024);

    $response = $this->put(route('entries.update', [$feed, $entry]), [
        'name' => 'Updated Entry',
        'file' => $newFile,
    ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->name)->toBe('Updated Entry');
    expect($entry->audio_url)->not->toBe($oldPath);
    expect($entry->audio_url)->not->toBeNull();
    expect($entry->transcription_path)->toBeNull();

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('local')->assertMissing('transcriptions/old.txt');
    Storage::disk('public')->assertExists($entry->audio_url);
    Bus::assertBatched(fn ($batch) => $batch->name === 'Process entry '.$entry->id);
});

it('clears transcription, summary, and chapters when deleting a file', function () {
    Storage::fake('local');
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('audios', 'public');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => $oldPath,
        'transcription_path' => 'transcriptions/old.txt',
        'summary' => 'Old Summary',
        'chapters' => [['title' => 'Old Chapter', 'startTime' => 0]],
    ]);

    $response = $this->put(route('entries.update', [$feed, $entry]), [
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
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();
    $oldFile = UploadedFile::fake()->create('old.mp3', 1024);
    $oldPath = $oldFile->store('audios', 'public');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Original Name',
        'audio_url' => $oldPath,
    ]);

    $response = $this->put(route('entries.update', [$feed, $entry]), [
        'name' => 'Updated Entry',
        'delete_file' => '1',
    ]);

    $response->assertRedirect();

    $entry->refresh();
    expect($entry->name)->toBe('Updated Entry');
    expect($entry->audio_url)->toBeNull();

    Storage::disk('public')->assertMissing($oldPath);
});

it('records the job batch on the entry when dispatching transcription', function () {
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);
    $path = $file->store('audios', 'public');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => $path,
    ]);

    $this->post(route('entries.produce', [$feed, $entry]))
        ->assertRedirect();

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(1);
});

it('accumulates a new batch record each time transcription is triggered', function () {
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);
    $path = $file->store('audios', 'public');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => $path,
    ]);

    $this->post(route('entries.produce', [$feed, $entry]));
    $this->post(route('entries.produce', [$feed, $entry]));

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(2);
});

it('returns 422 when regenerating transcription for entry without a file', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => null,
    ]);

    $this->post(route('entries.produce', [$feed, $entry]))
        ->assertStatus(422);

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(0);
});

it('can regenerate chapters and summary from an existing transcription', function () {
    Storage::fake('local');
    Storage::fake('public');
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create();

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'audios/audio.mp3',
        'transcription_path' => 'transcriptions/example.json',
    ]);

    $this->post(route('entries.produce', [$feed, $entry]), [
        'reuse_transcript' => true,
    ])
        ->assertRedirect();

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(1);
});

it('returns 422 when regenerating chapters and summary without a transcription', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'audios/audio.mp3',
        'transcription_path' => null,
    ]);

    $this->post(route('entries.produce', [$feed, $entry]), [
        'reuse_transcript' => true,
    ])
        ->assertStatus(422);

    expect(EntryJobBatch::where('entry_id', $entry->id)->count())->toBe(0);
});

it('deletes the file when an entry is destroyed', function () {
    Storage::fake('local');
    Storage::fake('public');
    $feed = Feed::factory()->for($this->user)->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);
    $path = $file->store('audios', 'public');

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => $path,
    ]);

    $response = $this->delete(route('entries.destroy', [$feed, $entry]));

    $response->assertRedirect();

    $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    Storage::disk('public')->assertMissing($path);
});

it('can view an entry', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
    ]);

    $this->get(route('entries.show', [$feed, $entry]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Entries/Show')
            ->has('entry')
        );
});

it('generates the correct rss description with AI summary and original description', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'summary' => 'This is the AI summary.',
        'original_summary' => 'This is the original summary.',
        'chapters' => [
            ['title' => 'Intro', 'startTime' => 0],
            ['title' => 'Main Topic', 'startTime' => 30],
        ],
    ]);

    $showNotesUrl = route('entries.show', [$feed, $entry]);
    $appUrl = url('/');

    $description = $entry->rss_description;

    expect($description)->toContain('<p>This is the AI summary.</p>')
        ->and($description)->toContain("<a href=\"{$showNotesUrl}\">Read episode transcription</a>")
        ->and($description)->toContain("Enhanced by <a href=\"{$appUrl}\">Podcasts Enhancer</a>")
        ->and($description)->toContain('<h2>Timestamps</h2>')
        ->and($description)->toContain('<li>00:00 - Intro</li>')
        ->and($description)->toContain('<li>00:30 - Main Topic</li>')
        ->and($description)->toContain('<h2>Show Notes</h2>')
        ->and($description)->toContain('<p>This is the original summary.</p>');
});

it('generates the correct rss description when original summary is missing', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'summary' => 'This is just an AI summary.',
        'chapters' => [],
    ]);

    $description = $entry->rss_description;

    expect($description)->toContain('<p>This is just an AI summary.</p>')
        ->and($description)->not()->toContain('<h2>Show Notes</h2>')
        ->and($description)->not()->toContain('<h2>Timestamps</h2>');
});

it('does not include enhancement links or enhanced by text when an entry is not enhanced', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'summary' => null,
        'transcription_path' => null,
        'chapters' => null,
    ]);

    $description = $entry->rss_description;
    $appUrl = url('/');
    $showNotesUrl = route('entries.show', [$feed, $entry]);

    expect($description)->not->toContain("Enhanced by <a href=\"{$appUrl}\">Podcasts Enhancer</a>")
        ->and($description)->not->toContain("<a href=\"{$showNotesUrl}\">Read episode transcription</a>")
        ->and($description)->toContain("<a href=\"{$showNotesUrl}\">Enhance with Podcasts Enhancer</a>");
});

it('preserves HTML in original summary and summary for rss_description', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'summary' => 'AI <strong>Summary</strong>',
        'original_summary' => 'Original <a href="http://example.com">Link</a>',
    ]);

    $description = $entry->rss_description;

    expect($description)->toContain('AI <strong>Summary</strong>')
        ->and($description)->toContain('Original <a href="http://example.com">Link</a>');
});

it('trims original descriptions before importing in the database', function () {
    Bus::fake();
    $feed = Feed::factory()->for($this->user)->create(['rss_url' => null]);

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <item>
            <title>Episode 1 Unique</title>
            <guid>guid1</guid>
            <description>   This is a description with spaces.   </description>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
        <item>
            <title>Episode 2 Unique</title>
            <guid>guid2</guid>
            <description>   </description>
            <enclosure url="https://example.com/audio2.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $this->post(route('feeds.import-rss.store', $feed), [
        'url' => 'https://example.com/feed.xml',
        'episodes' => ['guid1', 'guid2'],
    ])
        ->assertSessionHas('success', '2 episodes imported successfully.');

    $entry1 = $feed->entries()->where('name', 'Episode 1 Unique')->first();
    expect($entry1)->not->toBeNull();
    expect($entry1->original_summary)->toBe('This is a description with spaces.');

    $entry2 = $feed->entries()->where('name', 'Episode 2 Unique')->first();
    expect($entry2)->not->toBeNull();
    expect($entry2->original_summary)->toBeEmpty();
});

it('does not show original description section if it is empty or whitespace', function () {
    $feed = Feed::factory()->for($this->user)->create();

    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'original_summary' => '   ',
    ]);

    expect($entry->rss_description)->not->toContain('<h2>Show Notes</h2>');

    $entry->original_summary = '';
    expect($entry->rss_description)->not->toContain('<h2>Show Notes</h2>');
});

it('generates a slug that does not exceed 255 characters even for very long names', function () {
    $feed = Feed::factory()->for($this->user)->create();
    $longName = str_repeat('a', 500);

    $entry = Entry::create([
        'feed_id' => $feed->id,
        'name' => $longName,
        'slug' => Entry::generateUniqueSlug($longName),
        'audio_url' => 'https://example.com/audio.mp3',
        'published_at' => now(),
    ]);

    expect(strlen($entry->slug))->toBeLessThanOrEqual(255);
});
