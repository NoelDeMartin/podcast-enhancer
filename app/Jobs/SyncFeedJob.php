<?php

namespace App\Jobs;

use App\Concerns\FetchesRssFeeds;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SyncFeedJob implements ShouldQueue
{
    use Batchable, Dispatchable, FetchesRssFeeds, InteractsWithQueue, Queueable;

    public function __construct(public int $feedId) {}

    public function handle(): void
    {
        $feed = Feed::withoutGlobalScopes()->findOrFail($this->feedId);

        if (! $feed->rss_url) {
            return;
        }

        $data = $this->fetchAndParseRss($feed->rss_url);

        $existingAudioUrls = $feed->entries()->pluck('audio_url')->filter()->toArray();
        $existingNames = $feed->entries()->pluck('name')->toArray();

        foreach ($data['episodes'] as $episodeData) {
            $audioUrl = $episodeData['audio_url'];
            $name = $episodeData['name'];

            if ($audioUrl && ! in_array($audioUrl, $existingAudioUrls) && ! in_array($name, $existingNames)) {
                $feed->entries()->create([
                    'name' => $name,
                    'slug' => Entry::generateUniqueSlug($name),
                    'audio_url' => $audioUrl,
                    'image_url' => $episodeData['image_url'] ?? null,
                    'original_summary' => $episodeData['summary'] ?? null,
                    'published_at' => $episodeData['published_at'],
                ]);
                $existingAudioUrls[] = $audioUrl;
                $existingNames[] = $name;
            }
        }

        $feed->update([
            'title' => $data['title'] ?? $feed->title,
            'description' => $data['description'] ?? $feed->description,
            'image_url' => $data['image_url'] ?? $feed->image_url,
            'last_synced_at' => now(),
        ]);
    }
}
