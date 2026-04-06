<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RssImportController extends Controller
{
    use Concerns\DispatchesBatches;

    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        try {
            $response = Http::get($request->url);

            if ($response->failed()) {
                return response()->json(['message' => 'Failed to fetch RSS feed.'], 422);
            }

            $xml = new \SimpleXMLElement($response->body());
            $episodes = [];

            foreach ($xml->channel->item as $item) {
                $audioUrl = null;
                if ($item->enclosure && $item->enclosure['url']) {
                    $audioUrl = (string) $item->enclosure['url'];
                }

                $episodes[] = [
                    'name' => (string) $item->title,
                    'summary' => (string) $item->description,
                    'audio_url' => $audioUrl,
                ];
            }

            return response()->json(['episodes' => $episodes]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid RSS feed format.'], 422);
        }
    }

    public function store(Request $request, Feed $feed): RedirectResponse
    {
        $request->validate([
            'episodes' => ['required', 'array'],
            'episodes.*.name' => ['required', 'string'],
            'episodes.*.audio_url' => ['required', 'url'],
            'episodes.*.summary' => ['nullable', 'string'],
        ]);

        foreach ($request->episodes as $episodeData) {
            $entry = $feed->entries()->create([
                'name' => $episodeData['name'],
                'audio_url' => $episodeData['audio_url'],
                'summary' => $episodeData['summary'] ? "<original_summary>{$episodeData['summary']}</original_summary>" : null,
            ]);

            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', count($request->episodes).' episodes imported successfully.');
    }
}
