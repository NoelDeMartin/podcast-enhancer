<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Concerns\FetchesRssFeeds;
use App\Http\Requests\FetchRssRequest;
use App\Http\Requests\StoreRssImportRequest;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RssImportController extends Controller
{
    use DispatchesBatches;
    use FetchesRssFeeds;

    public function fetch(FetchRssRequest $request, Feed $feed): JsonResponse
    {
        Gate::authorize('update', $feed);

        try {
            $data = $this->fetchAndParseRss($request->url);

            return response()->json(['episodes' => $data['episodes']]);
        } catch (\Exception $e) {
            $message = $e->getMessage() === 'Failed to fetch RSS feed.'
                ? 'Failed to fetch RSS feed.'
                : 'Invalid RSS feed format.';

            return response()->json(['message' => $message], 422);
        }
    }

    public function store(StoreRssImportRequest $request, Feed $feed): RedirectResponse
    {
        Gate::authorize('update', $feed);

        if ($feed->rss_url) {
            abort(403, 'Manual RSS imports are not allowed for synchronized feeds.');
        }

        foreach ($request->episodes as $episodeData) {
            $publishedAt = filled($episodeData['published_at'] ?? null)
                ? $episodeData['published_at']
                : now();

            $entry = $feed->entries()->create([
                'name' => $episodeData['name'],
                'slug' => Entry::generateUniqueSlug($episodeData['name']),
                'audio_url' => $episodeData['audio_url'],
                'image_url' => $episodeData['image_url'] ?? null,
                'original_summary' => $episodeData['summary'] ?? null,
                'published_at' => $publishedAt,
            ]);

            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', count($request->episodes).' episodes imported successfully.');
    }
}
