<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Http;

trait FetchesRssFeeds
{
    /**
     * Fetch and parse an RSS feed URL.
     *
     * @return array{title: string, description: string, episodes: array}
     *
     * @throws \Exception
     */
    protected function fetchAndParseRss(string $url): array
    {
        $response = Http::get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch RSS feed.');
        }

        $xml = new \SimpleXMLElement($response->body());

        $title = (string) $xml->channel->title;
        $description = (string) $xml->channel->description;
        $episodes = [];

        foreach ($xml->channel->item as $item) {
            $audioUrl = null;
            if ($item->enclosure && $item->enclosure['url']) {
                $audioUrl = (string) $item->enclosure['url'];
            }

            $episodes[] = [
                'name' => (string) $item->title,
                'summary' => (string) $item->description,
                'audio_url' => $audioUrl,
            ];
        }

        return [
            'title' => $title,
            'description' => $description,
            'episodes' => $episodes,
        ];
    }
}
