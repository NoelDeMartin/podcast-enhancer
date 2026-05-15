<?php

namespace App\Concerns;

use App\Models\Entry;
use App\Models\Feed;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

trait ImportsRssFeeds
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

        $itunes = $xml->channel->children('itunes', true);
        $explicit = (string) ($itunes->explicit ?? '');
        $categories = collect($itunes->category)
            ->flatMap(function ($category) {
                return collect([(string) $category->attributes()->text])
                    ->concat(collect($category->category)->map(fn ($sub) => (string) $sub->attributes()->text));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $episodes = [];

        foreach ($xml->channel->item as $item) {
            $audioUrl = (string) ($item->enclosure['url'] ?? '') ?: null;

            $episodeImageUrl = null;
            if ($item->children('itunes', true)->image) {
                $episodeImageUrl = (string) $item->children('itunes', true)->image->attributes()->href;
            } elseif (isset($item->image) && isset($item->image->url)) {
                $episodeImageUrl = (string) $item->image->url;
            }

            $pubDate = trim((string) ($item->pubDate ?? ''));
            $publishedAt = $pubDate !== '' ? CarbonImmutable::parse($pubDate) : null;

            $duration = null;
            if ($item->children('itunes', true)->duration) {
                $duration = $this->parseDuration((string) $item->children('itunes', true)->duration);
            }

            $episodes[] = [
                'guid' => (string) ($item->guid ?? ''),
                'name' => (string) $item->title,
                'summary' => trim((string) $item->description),
                'audio_url' => $audioUrl,
                'image_url' => $episodeImageUrl,
                'published_at' => $publishedAt,
                'duration' => $duration,
            ];
        }

        return [
            'title' => $title,
            'description' => $description,
            'image_url' => $imageUrl,
            'explicit' => $explicit !== '' ? $explicit : null,
            'categories' => ! empty($categories) ? $categories : null,
            'episodes' => $episodes,
        ];
    }

    protected function importEpisode(Feed $feed, array $episodeData): ?Entry
    {
        $audioUrl = $episodeData['audio_url'];
        $name = $episodeData['name'];

        if (! $audioUrl) {
            return null;
        }

        if ($feed->entries()->where('audio_url', $audioUrl)->exists() || $feed->entries()->where('name', $name)->exists()) {
            return null;
        }

        return $feed->entries()->create([
            'name' => $name,
            'slug' => Entry::generateUniqueSlug($name),
            'audio_url' => $audioUrl,
            'duration' => $episodeData['duration'] ?? null,
            'image_url' => $episodeData['image_url'] ?? null,
            'original_summary' => $episodeData['summary'] ?? null,
            'published_at' => $episodeData['published_at'] ?? now(),
        ]);
    }

    protected function parseDuration(string $duration): int
    {
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        $parts = explode(':', $duration);
        $seconds = 0;

        foreach ($parts as $part) {
            $seconds = $seconds * 60 + (int) $part;
        }

        return $seconds;
    }
}
