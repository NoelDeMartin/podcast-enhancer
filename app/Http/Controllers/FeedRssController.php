<?php

namespace App\Http\Controllers;

use App\Jobs\SyncFeedJob;
use App\Models\Feed;
use App\Models\Scopes\UserScope;
use Illuminate\Http\Response;

class FeedRssController extends Controller
{
    public function __invoke(string $feed): Response
    {
        $feed = Feed::withoutGlobalScope(UserScope::class)->where('slug', $feed)->firstOrFail();

        $shouldSync = $feed->rss_url &&
            $feed->sync_frequency &&
            (! $feed->last_synced_at || $feed->last_synced_at->addSeconds($feed->sync_frequency)->isPast());

        if ($shouldSync) {
            (new SyncFeedJob($feed))->handle();
            $feed = Feed::withoutGlobalScope(UserScope::class)->findOrFail($feed->id);
        }

        $entries = $feed->entries;
        $entries->each->setRelation('feed', $feed);

        return response()
            ->view('feeds.rss', compact('feed', 'entries'))
            ->header('Content-Type', 'text/xml');
    }
}
