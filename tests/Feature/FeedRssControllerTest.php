<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Support\Facades\Storage;

it('generates an rss feed for a feed', function () {
    Storage::fake('public');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode 1',
        'summary' => 'This is the first episode summary.',
        'audio_url' => 'audios/audio.mp3',
        'duration' => 3723,
    ]);

    Storage::disk('public')->put('audios/audio.mp3', 'dummy content');

    $this->get(route('feeds.rss', $feed))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/xml; charset=UTF-8')
        ->assertSee('<title>My Podcast</title>', false)
        ->assertSee('<title>Episode 1</title>', false)
        ->assertSee('<description><![CDATA[<p>This is the first episode summary.</p>', false)
        ->assertSee('Read episode transcription</a>', false)
        ->assertSee('Enhanced by <a href="'.url('/').'">Podcast Enhancer</a>', false)
        ->assertSee('<pubDate>'.$entry->published_at->toRfc2822String().'</pubDate>', false)
        ->assertSee('<itunes:duration>3723</itunes:duration>', false)
        ->assertSee(asset(Storage::disk('public')->url($entry->audio_url)), false)
        ->assertSee('length="13"', false); // "dummy content" is 13 bytes
});

it('includes podcast chapters in rss when available', function () {
    Storage::fake('public');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode With Chapters',
        'audio_url' => 'audios/audio.mp3',
        'chapters' => [
            ['title' => 'Intro', 'startTime' => 0],
            ['title' => 'Main Topic', 'startTime' => 60],
        ],
    ]);

    Storage::disk('public')->put('audios/audio.mp3', 'dummy content');

    $this->get(route('feeds.rss', $feed))
        ->assertSuccessful()
        ->assertSee('xmlns:psc="http://podlove.org/simple-chapters"', false)
        ->assertSee('<li>00:00 - Intro</li>', false)
        ->assertSee('<li>01:00 - Main Topic</li>', false)
        ->assertSee('<content:encoded><![CDATA[', false)
        ->assertSee('<psc:chapters version="1.2">', false)
        ->assertSee('<psc:chapter start="00:00:00" title="Intro" />', false)
        ->assertSee('<psc:chapter start="00:01:00" title="Main Topic" />', false);
});

it('omits podcast chapters in rss when not available', function () {
    Storage::fake('public');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode Without Chapters',
        'audio_url' => 'audios/audio.mp3',
        'chapters' => null,
    ]);

    Storage::disk('public')->put('audios/audio.mp3', 'dummy content');

    $this->get(route('feeds.rss', $feed))
        ->assertSuccessful()
        ->assertDontSee('<psc:chapters', false);
});
