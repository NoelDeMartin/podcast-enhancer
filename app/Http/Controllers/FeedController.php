<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Models\FailedJob;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function store(StoreFeedRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $request->file('image_file')->store('images', 'public');
        }

        $validated['slug'] = Feed::generateUniqueSlug($validated['title']);

        $request->user()->feeds()->create($validated);

        return redirect()->back()->with('success', 'Feed created successfully.');
    }

    public function show(Feed $feed): Response
    {
        $feed->load(['entries.latestJobBatch', 'latestJobBatch']);

        $this->loadFailedJobDetails($feed);

        $feed->entries->each(function ($entry) {
            $entry->transcription = $entry->transcription_path ? Storage::get($entry->transcription_path) : null;
        });

        return Inertia::render('Feeds/Show', [
            'feed' => $feed,
        ]);
    }

    private function loadFailedJobDetails(Feed $feed): void
    {
        $jobBatches = $feed->entries
            ->pluck('latestJobBatch.jobBatch')
            ->filter();

        if ($feed->latestJobBatch?->jobBatch) {
            $jobBatches->push($feed->latestJobBatch->jobBatch);
        }

        $failedJobIds = $jobBatches
            ->flatMap(fn ($batch) => $batch->failed_job_ids ?? [])
            ->unique()
            ->values()
            ->all();

        if (empty($failedJobIds)) {
            return;
        }

        $failedJobs = FailedJob::whereIn('uuid', $failedJobIds)->get()->keyBy('uuid');

        $jobBatches->each(function ($batch) use ($failedJobs) {
            $batch->setRelation(
                'failedJobDetails',
                collect($batch->failed_job_ids ?? [])->map(fn ($uuid) => $failedJobs->get($uuid))->filter()->values(),
            );
        });
    }

    public function update(UpdateFeedRequest $request, Feed $feed): RedirectResponse
    {
        $validated = $request->validated();

        if ($feed->rss_url) {
            unset($validated['title'], $validated['description'], $validated['image_url'], $validated['image_file'], $validated['delete_image_file']);
        }

        if (isset($validated['image_file'])) {
            if ($feed->image_url && ! $feed->image_is_external) {
                Storage::disk('public')->delete($feed->image_url);
            }
            $validated['image_url'] = $request->file('image_file')->store('images', 'public');
        } elseif ($request->boolean('delete_image_file') && $feed->image_url) {
            if (! $feed->image_is_external) {
                Storage::disk('public')->delete($feed->image_url);
            }
            $validated['image_url'] = null;
        } elseif (isset($validated['image_url']) && $validated['image_url'] !== $feed->image_url) {
            if ($feed->image_url && ! $feed->image_is_external) {
                Storage::disk('public')->delete($feed->image_url);
            }
        }
        unset($validated['image_file'], $validated['delete_image_file']);

        if (isset($validated['sync_frequency']) && (int) $validated['sync_frequency'] === 0) {
            $validated['sync_frequency'] = null;
        }

        $feed->update($validated);

        return redirect()->back()->with('success', 'Feed updated successfully.');
    }

    public function destroy(Feed $feed): RedirectResponse
    {
        if ($feed->image_url && ! $feed->image_is_external) {
            Storage::disk('public')->delete($feed->image_url);
        }

        $feed->delete();

        return redirect()->route('dashboard')->with('success', 'Feed deleted successfully.');
    }
}
