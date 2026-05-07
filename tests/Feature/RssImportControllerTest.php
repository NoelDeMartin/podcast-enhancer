<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('can fetch episodes from an RSS feed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <item>
            <title>Episode 1</title>
            <guid>guid1</guid>
            <description>Summary 1</description>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
        <item>
            <title>Episode 2</title>
            <guid>guid2</guid>
            <description>Summary 2</description>
            <enclosure url="https://example.com/audio2.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('feeds.import-rss.fetch', $feed), [
            'url' => 'https://example.com/feed.xml',
        ]);

    $response->assertOk()
        ->assertJsonCount(2, 'episodes')
        ->assertJsonPath('episodes.0.name', 'Episode 1')
        ->assertJsonPath('episodes.0.guid', 'guid1')
        ->assertJsonPath('episodes.0.audio_url', 'https://example.com/audio1.mp3')
        ->assertJsonPath('episodes.0.summary', 'Summary 1');
});

it('can import selected episodes', function () {
    Bus::fake();

    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <item>
            <title>Episode 1</title>
            <guid>guid1</guid>
            <description>Summary 1</description>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $response = $this->actingAs($user)
        ->post(route('feeds.import-rss.store', $feed), [
            'url' => 'https://example.com/feed.xml',
            'episodes' => ['guid1'],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('entries', [
        'feed_id' => $feed->id,
        'name' => 'Episode 1',
        'audio_url' => 'https://example.com/audio1.mp3',
        'original_summary' => 'Summary 1',
    ]);

    $entry = Entry::where('feed_id', $feed->id)->where('name', 'Episode 1')->first();
    expect($entry)->not->toBeNull();
    expect($entry->published_at)->not->toBeNull();

    Bus::assertNothingBatched();
});

it('enforces trust boundary and ignores client metadata', function () {
    Bus::fake();

    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <item>
            <title>Real Title</title>
            <guid>guid1</guid>
            <description>Real Summary</description>
            <enclosure url="https://example.com/real-audio.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    // Client tries to send "wrong" metadata - but backend only accepts url and episodes identifiers
    $response = $this->actingAs($user)
        ->post(route('feeds.import-rss.store', $feed), [
            'url' => 'https://example.com/feed.xml',
            'episodes' => ['guid1'],
            'name' => 'Fake Title', // Should be ignored
            'audio_url' => 'https://fake.com/audio.mp3', // Should be ignored
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('entries', [
        'feed_id' => $feed->id,
        'name' => 'Real Title',
        'audio_url' => 'https://example.com/real-audio.mp3',
        'original_summary' => 'Real Summary',
    ]);

    $this->assertDatabaseMissing('entries', [
        'name' => 'Fake Title',
    ]);
});

it('fails when requested identifiers are missing in the feed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <item>
            <title>Episode 1</title>
            <guid>guid1</guid>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('feeds.import-rss.store', $feed), [
            'url' => 'https://example.com/feed.xml',
            'episodes' => ['guid1', 'non-existent-guid'],
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['episodes']);
});

it('enforces maximum selection limit', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $episodes = array_map(fn ($i) => "guid{$i}", range(1, 51));

    $response = $this->actingAs($user)
        ->postJson(route('feeds.import-rss.store', $feed), [
            'url' => 'https://example.com/feed.xml',
            'episodes' => $episodes,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['episodes']);
});

it('forbids manual import for synchronized feeds', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create([
        'rss_url' => 'https://example.com/synced-feed.xml',
    ]);

    $response = $this->actingAs($user)
        ->post(route('feeds.import-rss.store', $feed), [
            'url' => 'https://example.com/feed.xml',
            'episodes' => ['guid1'],
        ]);

    $response->assertForbidden();
});

it('fails to fetch invalid RSS feed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    Http::fake([
        'https://example.com/invalid.xml' => Http::response('invalid xml', 200),
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('feeds.import-rss.fetch', $feed), [
            'url' => 'https://example.com/invalid.xml',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Invalid RSS feed format.');
});
