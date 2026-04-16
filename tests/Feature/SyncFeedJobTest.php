<?php

namespace Tests\Feature;

use App\Jobs\SyncFeedJob;
use App\Models\Feed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('updates last_synced_at when SyncFeedJob runs', function () {
    $feed = Feed::factory()->create([
        'rss_url' => 'https://example.com/feed.xml',
        'last_synced_at' => null,
    ]);

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Podcast Title</title>
        <item>
            <title>Episode 1</title>
            <enclosure url="https://example.com/audio1.mp3" type="audio/mpeg"/>
            <pubDate>Tue, 07 Apr 2026 12:34:56 +0000</pubDate>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/feed.xml' => Http::response($rssContent, 200),
    ]);

    $job = new SyncFeedJob($feed);
    $job->handle();

    $feed->refresh();
    expect($feed->last_synced_at)->not->toBeNull();
    expect($feed->last_synced_at->isToday())->toBeTrue();
});

it('does not update last_synced_at if rss_url is missing', function () {
    $feed = Feed::factory()->create([
        'rss_url' => null,
        'last_synced_at' => null,
    ]);

    $job = new SyncFeedJob($feed);
    $job->handle();

    $feed->refresh();
    expect($feed->last_synced_at)->toBeNull();
});
