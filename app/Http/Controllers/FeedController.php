<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Models\FailedJob;
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
        $feed->load('entries.latestJobBatch');

        $this->loadFailedJobDetails($feed);

        return Inertia::render('Feeds/Show', [
            'feed' => $feed,
        ]);
    }

    private function loadFailedJobDetails(Feed $feed): void
    {
        $jobBatches = $feed->entries
            ->pluck('latestJobBatch.jobBatch')
            ->filter();

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
        $feed->update($request->validated());

        return redirect()->back()->with('success', 'Feed updated successfully.');
    }

    public function destroy(Feed $feed): RedirectResponse
    {
        $feed->delete();

        return redirect()->route('dashboard')->with('success', 'Feed deleted successfully.');
    }
}
