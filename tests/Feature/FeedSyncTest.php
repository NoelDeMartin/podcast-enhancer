<?php

namespace Tests\Feature;

use App\Jobs\PrepareTranscriptionJob;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
    Bus::fake();
    $this->user = User::factory()->create();
});

it('can import feed from rss without dispatching jobs', function () {
    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Podcast Title</title>
        <description>Podcast Description</description>
        <item>
            <title>Episode 1</title>
            <pubDate>Tue, 07 Apr 2026 12:34:56 +0000</pubDate>
            <description>Summary 1</description>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('feeds.sync.store'), [
            'rss_url' => 'https://example.com/feed.xml',
            // Omit title and description to test XML parsing
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('feeds', [
        'title' => 'Podcast Title',
        'description' => 'Podcast Description',
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $feed = Feed::where('rss_url', 'https://example.com/feed.xml')->first();
    expect($feed->last_synced_at)->not->toBeNull();
    expect($feed->last_synced_at->isToday())->toBeTrue();

    $this->assertDatabaseHas('entries', [
        'feed_id' => $feed->id,
        'name' => 'Episode 1',
        'audio_url' => 'https://example.com/audio1.mp3',
    ]);

    $entry = Entry::where('feed_id', $feed->id)->where('name', 'Episode 1')->first();
    expect($entry)->not->toBeNull();
    expect($entry->published_at)->not->toBeNull();
    expect($entry->published_at->equalTo(CarbonImmutable::parse('Tue, 07 Apr 2026 12:34:56 +0000')))->toBeTrue();

    Bus::assertNotDispatched(PrepareTranscriptionJob::class);
    Bus::assertNotDispatched(ProduceEntryJob::class);
});

it('can synchronize existing feed without duplicates via background job', function () {
    $feed = Feed::factory()->for($this->user)->create([
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    // Create an existing entry
    $feed->entries()->create([
        'name' => 'Episode 1',
        'slug' => Entry::generateUniqueSlug('Episode 1'),
        'audio_url' => 'https://example.com/audio1.mp3',
        'published_at' => now(),
    ]);

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Podcast Title</title>
        <item>
            <title>Episode 1</title>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
        <item>
            <title>Episode 2</title>
            <pubDate>Wed, 08 Apr 2026 10:00:00 +0000</pubDate>
            <enclosure url="https://example.com/audio2.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('feeds.sync', $feed));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Feed synchronization queued successfully.');

    Bus::assertBatched(function (PendingBatch $batch) use ($feed) {
        return $batch->jobs->count() === 1 &&
               $batch->jobs->first()->feed->id === $feed->id &&
               $batch->name === 'Sync feed '.$feed->id;
    });

    $this->assertDatabaseHas('feed_job_batches', [
        'feed_id' => $feed->id,
    ]);
});

it('blocks manual entry creation for synchronized feeds', function () {
    $feed = Feed::factory()->for($this->user)->create([
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('entries.store', $feed), [
            'feed_id' => $feed->id,
            'name' => 'Manual Episode',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ]);

    $response->assertStatus(403);
});

it('blocks manual entry updates for synchronized feeds', function () {
    $feed = Feed::factory()->for($this->user)->create([
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $entry = $feed->entries()->create([
        'name' => 'Episode 1',
        'slug' => Entry::generateUniqueSlug('Episode 1'),
        'audio_url' => 'https://example.com/audio1.mp3',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('entries.update', [$feed, $entry]), [
            'name' => 'Updated Episode',
        ]);

    $response->assertStatus(403);
});

it('blocks manual entry deletion for synchronized feeds', function () {
    $feed = Feed::factory()->for($this->user)->create([
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $entry = $feed->entries()->create([
        'name' => 'Episode 1',
        'slug' => Entry::generateUniqueSlug('Episode 1'),
        'audio_url' => 'https://example.com/audio1.mp3',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('entries.destroy', [$feed, $entry]));

    $response->assertStatus(403);
});
