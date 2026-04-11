<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedSyncController extends Controller
{
    use Concerns\FetchesRssFeeds;

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'rss_url' => ['required', 'url'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $data = $this->fetchAndParseRss($request->rss_url);

            $feedTitle = $request->title ?: $data['title'];
            $feedDescription = $request->description ?: $data['description'];

            if (empty($feedTitle)) {
                return redirect()->back()->withErrors(['title' => 'Could not determine feed title from RSS. Please provide one.']);
            }

            $feed = Feed::create([
                'title' => $feedTitle,
                'description' => $feedDescription,
                'rss_url' => $request->rss_url,
            ]);

            $importedCount = 0;

            foreach ($data['episodes'] as $episodeData) {
                if ($episodeData['audio_url']) {
                    $feed->entries()->create([
                        'name' => $episodeData['name'],
                        'audio_url' => $episodeData['audio_url'],
                        'summary' => $episodeData['summary'] ? '<original_summary>'.$episodeData['summary'].'</original_summary>' : null,
                        'published_at' => $episodeData['published_at'],
                    ]);
                    $importedCount++;
                }
            }

            return redirect()->back()->with('success', "Feed created and {$importedCount} episodes imported successfully.");
        } catch (\Exception $e) {
            $errorKey = $e->getMessage() === 'Failed to fetch RSS feed.' ? 'rss_url' : 'rss_url';
            $errorMessage = $e->getMessage() === 'Failed to fetch RSS feed.' ? 'Failed to fetch RSS feed.' : 'Invalid RSS feed format.';

            return redirect()->back()->withErrors([$errorKey => $errorMessage]);
        }
    }

    public function sync(Feed $feed): RedirectResponse
    {
        if (! $feed->rss_url) {
            abort(400, 'This feed does not have an RSS URL configured for synchronization.');
        }

        try {
            $data = $this->fetchAndParseRss($feed->rss_url);

            $existingAudioUrls = $feed->entries()->pluck('audio_url')->filter()->toArray();
            $existingNames = $feed->entries()->pluck('name')->toArray();

            $importedCount = 0;

            foreach ($data['episodes'] as $episodeData) {
                $audioUrl = $episodeData['audio_url'];
                $name = $episodeData['name'];

                if ($audioUrl && ! in_array($audioUrl, $existingAudioUrls) && ! in_array($name, $existingNames)) {
                    $feed->entries()->create([
                        'name' => $name,
                        'audio_url' => $audioUrl,
                        'summary' => $episodeData['summary'] ? '<original_summary>'.$episodeData['summary'].'</original_summary>' : null,
                        'published_at' => $episodeData['published_at'],
                    ]);
                    $existingAudioUrls[] = $audioUrl;
                    $existingNames[] = $name;
                    $importedCount++;
                }
            }

            return redirect()->back()->with('success', "Feed synchronized. {$importedCount} new episodes imported.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage() === 'Failed to fetch RSS feed.' ? 'Failed to fetch RSS feed.' : 'Invalid RSS feed format.']);
        }
    }
}
