<?php

namespace App\Http\Controllers;

use App\Concerns\LoadsFailedJobs;
use App\Facades\Media;
use App\Http\Requests\StoreFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Models\Feed;
use App\Models\Scopes\UserScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    use LoadsFailedJobs;

    public function store(StoreFeedRequest $request): RedirectResponse
    {
        Gate::authorize('create', Feed::class);

        $validated = $request->validated();

        if ($request->hasFile('image_file')) {
            Gate::authorize('uploadFiles', Feed::class);
            $validated['image_url'] = Media::update('images', null, $request->file('image_file'));
        }

        $validated['slug'] = Feed::generateUniqueSlug($validated['title']);

        $request->user()->feeds()->create($validated);

        return redirect()->back()->with('success', 'Feed created successfully.');
    }

    public function show(string $feed): Response
    {
        $feed = Feed::withoutGlobalScope(UserScope::class)
            ->where('slug', $feed)
            ->firstOrFail();

        Gate::authorize('view', $feed);

        $feed->load(['entries.latestJobBatch', 'latestJobBatch']);

        $this->loadModelFailedJobDetails($feed);

        $user = auth()->user();

        $feed->entries->each(function ($entry) use ($user) {
            $entry->can = [
                'produce' => $user?->can('produce', $entry) ?? false,
                'regenerate' => $user?->can('regenerate', $entry) ?? false,
            ];
        });

        return Inertia::render('Feeds/Show', [
            'feed' => $feed,
            'can' => [
                'update' => $user?->can('update', $feed) ?? false,
                'delete' => $user?->can('delete', $feed) ?? false,
                'sync' => $user?->can('sync', $feed) ?? false,
                'uploadFiles' => $user?->can('uploadFiles', Feed::class) ?? false,
            ],
        ]);
    }

    public function update(UpdateFeedRequest $request, Feed $feed): RedirectResponse
    {
        Gate::authorize('update', $feed);

        $validated = $request->validated();

        if ($feed->rss_url) {
            $validated = Arr::except($validated, ['title', 'description', 'image_url', 'image_file', 'delete_image_file']);
        }

        if ($feed->shouldUpdateImage($validated)) {
            if (isset($validated['image_file'])) {
                Gate::authorize('uploadFiles', $feed);
            }

            $validated['image_url'] = Media::update(
                'images',
                $feed->image_url,
                $validated['image_file'] ?? $validated['image_url'] ?? null,
                ! empty($validated['delete_image_file'])
            );
        }

        if (array_key_exists('sync_frequency', $validated)) {
            $validated['sync_frequency'] = $validated['sync_frequency'] ?: null;
        }

        $feed->update(Arr::except($validated, ['image_file', 'delete_image_file']));

        return redirect()->back()->with('success', 'Feed updated successfully.');
    }

    public function destroy(Feed $feed): RedirectResponse
    {
        Gate::authorize('delete', $feed);

        Media::delete($feed->image_url);

        $feed->delete();

        return redirect()->route('dashboard')->with('success', 'Feed deleted successfully.');
    }
}
