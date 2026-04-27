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

    $job = new SyncFeedJob($feed->id);
    $job->handle();

    $feed->refresh();
    expect($feed->last_synced_at)->not->toBeNull();
    expect($feed->last_synced_at->isToday())->toBeTrue();
});

it('updates feed title, description and image_url when SyncFeedJob runs', function () {
    $feed = Feed::factory()->create([
        'rss_url' => 'https://example.com/feed.xml',
        'title' => 'Old Title',
        'description' => 'Old Description',
        'image_url' => 'https://example.com/old-image.png',
    ]);

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>New Title</title>
        <description>New Description</description>
        <image>
            <url>https://example.com/new-image.png</url>
        </image>
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

    $job = new SyncFeedJob($feed->id);
    $job->handle();

    $feed->refresh();
    expect($feed->title)->toBe('New Title');
    expect($feed->description)->toBe('New Description');
    expect($feed->image_url)->toBe('https://example.com/new-image.png');
});

it('updates feed image_url from itunes:image when SyncFeedJob runs', function () {
    $feed = Feed::factory()->create([
        'rss_url' => 'https://example.com/feed.xml',
        'image_url' => 'https://example.com/old-image.png',
    ]);

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
    <channel>
        <title>Podcast Title</title>
        <itunes:image href="https://example.com/itunes-image.png" />
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

    $job = new SyncFeedJob($feed->id);
    $job->handle();

    $feed->refresh();
    expect($feed->image_url)->toBe('https://example.com/itunes-image.png');
});

it('does not update last_synced_at if rss_url is missing', function () {
    $feed = Feed::factory()->create([
        'rss_url' => null,
        'last_synced_at' => null,
    ]);

    $job = new SyncFeedJob($feed->id);
    $job->handle();

    $feed->refresh();
    expect($feed->last_synced_at)->toBeNull();
});

it('can handle long audio and image urls', function () {
    $feed = Feed::factory()->create([
        'rss_url' => 'https://example.com/rss.xml',
    ]);

    $longUrl = 'https://example.com/'.str_repeat('a', 1000).'.mp3';
    $longImageUrl = 'https://example.com/'.str_repeat('b', 1000).'.jpg';

    $rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
    <channel>
        <title>Test Feed</title>
        <description>Test Description</description>
        <item>
            <title>Test Episode</title>
            <description>Test Summary</description>
            <enclosure url="'.$longUrl.'" length="12345" type="audio/mpeg" />
            <itunes:image href="'.$longImageUrl.'" />
            <pubDate>Mon, 27 Apr 2026 10:00:00 +0000</pubDate>
        </item>
    </channel>
</rss>';

    Http::fake([
        'https://example.com/rss.xml' => Http::response($rssContent, 200),
    ]);

    (new SyncFeedJob($feed->id))->handle();

    $entry = $feed->entries()->first();
    expect($entry->audio_url)->toBe($longUrl);
    expect($entry->image_url)->toBe($longImageUrl);
});
