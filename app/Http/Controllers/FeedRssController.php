<?php

namespace App\Http\Controllers;

use App\Jobs\SyncFeedJob;
use App\Models\Feed;
use App\Models\Scopes\UserScope;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedRssController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $feed): Response
    {
        $feed = Feed::withoutGlobalScope(UserScope::class)->findOrFail($feed);

        if ($feed->rss_url && $feed->sync_frequency) {
            if (! $feed->last_synced_at || $feed->last_synced_at->addSeconds($feed->sync_frequency)->isPast()) {
                (new SyncFeedJob($feed))->handle();
                $feed = Feed::withoutGlobalScope(UserScope::class)->findOrFail($feed->id);
            }
        }

        $entries = $feed->entries;

        return response()
            ->view('feeds.rss', compact('feed', 'entries'))
            ->header('Content-Type', 'text/xml');
    }
}
