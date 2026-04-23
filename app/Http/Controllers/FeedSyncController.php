<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Concerns\FetchesRssFeeds;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class FeedSyncController extends Controller
{
    use DispatchesBatches, FetchesRssFeeds;

    public function store(): RedirectResponse
    {
        Gate::authorize('create', Feed::class);

        request()->validate([
            'rss_url' => ['required', 'url'],
            'sync_frequency' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $data = $this->fetchAndParseRss(request()->rss_url);

            $feedTitle = $data['title'];
            $feedDescription = $data['description'];

            if (empty($feedTitle)) {
                return redirect()->back()->withErrors(['rss_url' => 'Could not determine feed title from RSS.']);
            }

            $feed = request()->user()->feeds()->create([
                'title' => $feedTitle,
                'slug' => Feed::generateUniqueSlug($feedTitle),
                'description' => $feedDescription,
                'rss_url' => request()->rss_url,
                'image_url' => $data['image_url'] ?? null,
                'last_synced_at' => now(),
                'sync_frequency' => request()->sync_frequency ?: null,
            ]);

            $importedCount = 0;

            foreach ($data['episodes'] as $episodeData) {
                if ($episodeData['audio_url']) {
                    $feed->entries()->create([
                        'name' => $episodeData['name'],
                        'slug' => Entry::generateUniqueSlug($episodeData['name']),
                        'audio_url' => $episodeData['audio_url'],
                        'image_url' => $episodeData['image_url'] ?? null,
                        'original_summary' => $episodeData['summary'] ?? null,
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
        Gate::authorize('sync', $feed);

        if (! $feed->rss_url) {
            abort(400, 'This feed does not have an RSS URL configured for synchronization.');
        }

        $this->dispatchSyncBatch($feed);

        return redirect()->back()->with('success', 'Feed synchronization queued successfully.');
    }
}
