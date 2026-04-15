<?php

namespace App\Jobs;

use App\Concerns\FetchesRssFeeds;
use App\Models\Feed;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFeedJob implements ShouldQueue
{
    use Batchable, Dispatchable, FetchesRssFeeds, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Feed $feed) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->feed->rss_url) {
            return;
        }

        $data = $this->fetchAndParseRss($this->feed->rss_url);

        $existingAudioUrls = $this->feed->entries()->pluck('audio_url')->filter()->toArray();
        $existingNames = $this->feed->entries()->pluck('name')->toArray();

        foreach ($data['episodes'] as $episodeData) {
            $audioUrl = $episodeData['audio_url'];
            $name = $episodeData['name'];

            if ($audioUrl && ! in_array($audioUrl, $existingAudioUrls) && ! in_array($name, $existingNames)) {
                $this->feed->entries()->create([
                    'name' => $name,
                    'audio_url' => $audioUrl,
                    'image_url' => $episodeData['image_url'] ?? null,
                    'summary' => $episodeData['summary'] ? '<original_summary>'.$episodeData['summary'].'</original_summary>' : null,
                    'published_at' => $episodeData['published_at'],
                ]);
                $existingAudioUrls[] = $audioUrl;
                $existingNames[] = $name;
            }
        }
    }
}
