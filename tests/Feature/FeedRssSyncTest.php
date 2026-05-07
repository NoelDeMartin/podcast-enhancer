<?php

namespace Tests\Feature;

use App\Models\Feed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('syncs feed when rss is requested and a day has passed', function () {
    $feed = Feed::factory()->create([
        'title' => 'Test Feed',
        'rss_url' => 'https://example.com/feed.xml',
        'last_synced_at' => now()->subDays(2),
    ]);

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Test Feed</title>
        <item>
            <title>New Episode</title>
            <enclosure url="https://example.com/new.mp3" type="audio/mpeg"/>
            <pubDate>Thu, 16 Apr 2026 12:00:00 +0000</pubDate>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertStatus(200);
    $this->assertDatabaseHas('entries', [
        'feed_id' => $feed->id,
        'name' => 'New Episode',
    ]);

    $feed->refresh();
    expect($feed->last_synced_at->isToday())->toBeTrue();
    expect($feed->last_synced_at->isAfter(now()->subMinute()))->toBeTrue();
});

it('does not sync if a day has not passed', function () {
    $feed = Feed::factory()->create([
        'rss_url' => 'https://example.com/feed.xml',
        'last_synced_at' => now()->subHours(12),
    ]);

    Http::fake();

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertStatus(200);
    Http::assertNothingSent();
});
