<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Concerns\ImportsRssFeeds;
use App\Http\Requests\FetchRssRequest;
use App\Http\Requests\StoreRssImportRequest;
use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RssImportController extends Controller
{
    use DispatchesBatches, ImportsRssFeeds;

    public function fetch(FetchRssRequest $request, Feed $feed): JsonResponse
    {
        Gate::authorize('update', $feed);

        try {
            $data = $this->fetchAndParseRss($request->url);

            return response()->json(['episodes' => $data['episodes']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() === 'Failed to fetch RSS feed.'
                    ? 'Failed to fetch RSS feed.'
                    : 'Invalid RSS feed format.',
            ], 422);
        }
    }

    public function store(StoreRssImportRequest $request, Feed $feed): RedirectResponse
    {
        Gate::authorize('update', $feed);

        if ($feed->rss_url) {
            abort(403, 'Manual RSS imports are not allowed for synchronized feeds.');
        }

        try {
            $rssData = $this->fetchAndParseRss($request->url);
        } catch (\Exception) {
            throw ValidationException::withMessages(['url' => 'Failed to fetch or parse the RSS feed.']);
        }

        $matchedEpisodes = collect($rssData['episodes'])
            ->keyBy(fn ($episode) => $episode['guid'] ?: $episode['audio_url'])
            ->only($request->episodes);

        if ($matchedEpisodes->count() !== count($request->episodes)) {
            $missing = array_diff($request->episodes, $matchedEpisodes->keys()->all());

            throw ValidationException::withMessages([
                'episodes' => 'The following episodes could not be found in the feed: '.implode(', ', $missing),
            ]);
        }

        $count = $matchedEpisodes->filter(fn ($episode) => $this->importEpisode($feed, $episode))->count();

        return redirect()->back()->with('success', $count.' episodes imported successfully.');
    }
}
