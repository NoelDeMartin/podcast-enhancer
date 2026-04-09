<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RssImportController extends Controller
{
    use Concerns\DispatchesBatches;
    use Concerns\FetchesRssFeeds;

    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

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

    public function store(Request $request, Feed $feed): RedirectResponse
    {
        if ($feed->rss_url) {
            abort(403, 'Manual RSS imports are not allowed for synchronized feeds.');
        }

        $request->validate([
            'episodes' => ['required', 'array'],
            'episodes.*.name' => ['required', 'string'],
            'episodes.*.audio_url' => ['required', 'url'],
            'episodes.*.summary' => ['nullable', 'string'],
            'episodes.*.published_at' => ['nullable', 'date'],
        ]);

        foreach ($request->episodes as $episodeData) {
            $entry = $feed->entries()->create([
                'name' => $episodeData['name'],
                'audio_url' => $episodeData['audio_url'],
                'summary' => $episodeData['summary'] ? "<original_summary>{$episodeData['summary']}</original_summary>" : null,
                'published_at' => $episodeData['published_at'] ?? null,
            ]);

            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', count($request->episodes).' episodes imported successfully.');
    }
}
