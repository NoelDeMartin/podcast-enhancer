<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function store(StoreFeedRequest $request): RedirectResponse
    {
        Feed::create($request->validated());

        return redirect()->back()->with('success', 'Feed created successfully.');
    }

    public function show(Feed $feed): Response
    {
        $feed->load('entries');

        return Inertia::render('Feeds/Show', [
            'feed' => $feed,
        ]);
    }

    public function update(UpdateFeedRequest $request, Feed $feed): RedirectResponse
    {
        $feed->update($request->validated());

        return redirect()->back()->with('success', 'Feed updated successfully.');
    }

    public function destroy(Feed $feed): RedirectResponse
    {
        $feed->delete();

        return redirect()->route('dashboard')->with('success', 'Feed deleted successfully.');
    }
}
