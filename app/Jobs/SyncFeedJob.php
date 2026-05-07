<?php

namespace App\Jobs;

use App\Concerns\ImportsRssFeeds;
use App\Models\Feed;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SyncFeedJob implements ShouldQueue
{
    use Batchable, Dispatchable, ImportsRssFeeds, InteractsWithQueue, Queueable;

    public function __construct(public int $feedId) {}

    public function handle(): void
    {
        $feed = Feed::withoutGlobalScopes()->findOrFail($this->feedId);

        if (! $feed->rss_url) {
            return;
        }

        $data = $this->fetchAndParseRss($feed->rss_url);

        foreach ($data['episodes'] as $episodeData) {
            $this->importEpisode($feed, $episodeData);
        }

        $feed->update([
            'title' => $data['title'] ?? $feed->title,
            'description' => $data['description'] ?? $feed->description,
            'image_url' => $data['image_url'] ?? $feed->image_url,
            'last_synced_at' => now(),
        ]);
    }
}
