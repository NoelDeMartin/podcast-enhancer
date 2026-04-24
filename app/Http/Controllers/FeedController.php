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

        $this->loadFeedFailedJobDetails($feed);

        $feed->entries->each(function ($entry) {
            $entry->can = [
                'produce' => request()->user()?->can('produce', $entry) ?? false,
                'regenerate' => request()->user()?->can('regenerate', $entry) ?? false,
            ];
        });

        return Inertia::render('Feeds/Show', [
            'feed' => $feed,
            'can' => [
                'update' => request()->user()?->can('update', $feed) ?? false,
                'delete' => request()->user()?->can('delete', $feed) ?? false,
                'sync' => request()->user()?->can('sync', $feed) ?? false,
                'uploadFiles' => request()->user()?->can('uploadFiles', Feed::class) ?? false,
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

        if ($this->shouldUpdateImage($validated, $feed)) {
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

        if (isset($validated['sync_frequency']) && (int) $validated['sync_frequency'] === 0) {
            $validated['sync_frequency'] = null;
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

    private function loadFeedFailedJobDetails(Feed $feed): void
    {
        $jobBatches = $feed->entries
            ->pluck('latestJobBatch.jobBatch')
            ->filter();

        if ($feed->latestJobBatch?->jobBatch) {
            $jobBatches->push($feed->latestJobBatch->jobBatch);
        }

        if ($jobBatches->isNotEmpty()) {
            $this->loadFailedJobDetails($jobBatches);
        }
    }

    private function shouldUpdateImage(array $validated, Feed $feed): bool
    {
        return array_key_exists('image_file', $validated)
            || ! empty($validated['delete_image_file'])
            || (array_key_exists('image_url', $validated) && $validated['image_url'] !== $feed->image_url);
    }
}
