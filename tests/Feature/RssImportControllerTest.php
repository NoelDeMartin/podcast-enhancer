<?php

namespace Tests\Feature;

use App\Jobs\PrepareTranscriptionJob;
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
            <description>Summary 1</description>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
        </item>
        <item>
            <title>Episode 2</title>
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
        ->assertJsonPath('episodes.0.audio_url', 'https://example.com/audio1.mp3')
        ->assertJsonPath('episodes.0.summary', 'Summary 1');
});

it('can import selected episodes', function () {
    Bus::fake();

    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $episodes = [
        [
            'name' => 'Episode 1',
            'summary' => 'Summary 1',
            'audio_url' => 'https://example.com/audio1.mp3',
            'published_at' => '2026-04-07 12:34:56',
        ],
    ];

    $response = $this->actingAs($user)
        ->post(route('feeds.import-rss.store', $feed), [
            'episodes' => $episodes,
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

    Bus::assertBatched(function ($batch) {
        return $batch->jobs->first() instanceof PrepareTranscriptionJob;
    });
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

it('fails to import episode without audio url', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $episodes = [
        [
            'name' => 'Episode without audio',
            'summary' => 'Summary',
            'audio_url' => null,
        ],
    ];

    $response = $this->actingAs($user)
        ->postJson(route('feeds.import-rss.store', $feed), [
            'episodes' => $episodes,
        ]);

    $response->assertStatus(422);
});

it('imports episode without published_at using current time', function () {
    Bus::fake();

    $this->travelTo(now()->startOfSecond());

    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $episodes = [
        [
            'name' => 'Episode 1',
            'summary' => 'Summary 1',
            'audio_url' => 'https://example.com/audio1.mp3',
        ],
    ];

    $response = $this->actingAs($user)
        ->post(route('feeds.import-rss.store', $feed), [
            'episodes' => $episodes,
        ]);

    $response->assertRedirect();

    $entry = Entry::where('feed_id', $feed->id)->where('name', 'Episode 1')->first();
    expect($entry)->not->toBeNull();
    expect($entry->published_at->equalTo(now()))->toBeTrue();
});
