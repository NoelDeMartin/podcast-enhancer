<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Concerns\ImportsRssFeeds;
use App\Http\Requests\StoreFeedSyncRequest;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class FeedSyncController extends Controller
{
    use DispatchesBatches, ImportsRssFeeds;

    public function store(StoreFeedSyncRequest $request): RedirectResponse
    {
        Gate::authorize('create', Feed::class);

        try {
            $data = $this->fetchAndParseRss($request->rss_url);

            $feedTitle = $data['title'];
            $feedDescription = $data['description'];

            if (empty($feedTitle)) {
                return redirect()->back()->withErrors(['rss_url' => 'Could not determine feed title from RSS.']);
            }

            $feed = $request->user()->feeds()->create([
                'title' => $feedTitle,
                'slug' => Feed::generateUniqueSlug($feedTitle),
                'description' => $feedDescription,
                'rss_url' => $request->rss_url,
                'image_url' => $data['image_url'] ?? null,
                'last_synced_at' => now(),
            ]);

            $imported = $this->importEpisodes($feed, $data['episodes']);

            return redirect()->back()->with('success', "Feed created and {$imported->count()} episodes imported successfully.");
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() === 'Failed to fetch RSS feed.' ? 'Failed to fetch RSS feed.' : 'Invalid RSS feed format.';

            return redirect()->back()->withErrors(['rss_url' => $errorMessage]);
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
