<?php

namespace App\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

trait FetchesRssFeeds
{
    protected function fetchAndParseRss(string $url): array
    {
        $response = Http::get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch RSS feed.');
        }

        $xml = new \SimpleXMLElement($response->body());

        $title = (string) $xml->channel->title;
        $description = (string) $xml->channel->description;

        $imageUrl = null;
        if (isset($xml->channel->image) && isset($xml->channel->image->url)) {
            $imageUrl = (string) $xml->channel->image->url;
        } elseif ($xml->channel->children('itunes', true)->image) {
            $imageUrl = (string) $xml->channel->children('itunes', true)->image->attributes()->href;
        }

        $episodes = [];

        foreach ($xml->channel->item as $item) {
            $audioUrl = (string) ($item->enclosure['url'] ?? '');
            if (empty($audioUrl)) {
                $audioUrl = null;
            }

            $episodeImageUrl = null;
            if ($item->children('itunes', true)->image) {
                $episodeImageUrl = (string) $item->children('itunes', true)->image->attributes()->href;
            } elseif (isset($item->image) && isset($item->image->url)) {
                $episodeImageUrl = (string) $item->image->url;
            }

            $pubDate = trim((string) ($item->pubDate ?? ''));
            $publishedAt = $pubDate !== '' ? CarbonImmutable::parse($pubDate) : null;

            $episodes[] = [
                'name' => (string) $item->title,
                'summary' => trim((string) $item->description),
                'audio_url' => $audioUrl,
                'image_url' => $episodeImageUrl,
                'published_at' => $publishedAt,
            ];
        }

        return [
            'title' => $title,
            'description' => $description,
            'image_url' => $imageUrl,
            'episodes' => $episodes,
        ];
    }
}
