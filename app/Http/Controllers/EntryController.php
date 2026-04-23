<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use App\Models\FailedJob;
use App\Models\Feed;
use App\Models\Scopes\UserScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EntryController extends Controller
{
    use DispatchesBatches;

    public function show(string $feed, string $entry): Response
    {
        $feed = Feed::withoutGlobalScope(UserScope::class)
            ->where('slug', $feed)
            ->firstOrFail();

        $entry = Entry::withoutGlobalScope(UserScope::class)
            ->where('slug', $entry)
            ->where('feed_id', $feed->id)
            ->firstOrFail();

        Gate::authorize('view', $entry);

        $entry->load(['feed' => fn ($query) => $query->withoutGlobalScope(UserScope::class), 'latestJobBatch']);

        $this->loadFailedJobDetails($entry);

        $entry->transcription = $entry->transcription_path ? Storage::get($entry->transcription_path) : null;

        $entry->can = [
            'produce' => request()->user()?->can('produce', $entry) ?? false,
            'regenerate' => request()->user()?->can('regenerate', $entry) ?? false,
        ];

        return Inertia::render('Entries/Show', [
            'entry' => $entry,
            'can' => [
                'update' => request()->user()?->can('update', $entry) ?? false,
                'delete' => request()->user()?->can('delete', $entry) ?? false,
                'uploadFiles' => request()->user()?->can('uploadFiles', Entry::class) ?? false,
            ],
        ]);
    }

    private function loadFailedJobDetails(Entry $entry): void
    {
        $batch = $entry->latestJobBatch?->jobBatch;

        if (! $batch || empty($batch->failed_job_ids)) {
            return;
        }

        $failedJobs = FailedJob::whereIn('uuid', $batch->failed_job_ids)->get()->keyBy('uuid');

        $batch->setRelation(
            'failedJobDetails',
            collect($batch->failed_job_ids)->map(fn ($uuid) => $failedJobs->get($uuid))->filter()->values(),
        );
    }

    public function store(StoreEntryRequest $request, Feed $feed): RedirectResponse
    {
        Gate::authorize('update', $feed);

        if ($feed->rss_url) {
            abort(403, 'Manual entries cannot be added to a synchronized feed.');
        }

        $validated = $request->validated();

        if ($request->hasFile('file')) {
            Gate::authorize('uploadFiles', Entry::class);
            $validated['audio_url'] = $request->file('file')->store('audios', 'public');
        }

        if ($request->hasFile('image_file')) {
            Gate::authorize('uploadFiles', Entry::class);
            $validated['image_url'] = $request->file('image_file')->store('images', 'public');
        }

        $validated['slug'] = Entry::generateUniqueSlug($validated['name']);

        $entry = $feed->entries()->create($validated);

        if ($entry->audio_url) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(UpdateEntryRequest $request, Feed $feed, string $entry): RedirectResponse
    {
        $entry = Entry::where('slug', $entry)->where('feed_id', $feed->id)->firstOrFail();

        Gate::authorize('update', $entry);

        if ($feed->rss_url) {
            abort(403, 'Entries in a synchronized feed cannot be modified manually.');
        }

        $validated = $request->validated();

        $fileChanged = false;

        if ($request->hasFile('file')) {
            Gate::authorize('uploadFiles', $entry);
            if ($entry->audio_url && ! $entry->audio_is_external) {
                Storage::disk('public')->delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
                $validated['transcription_path'] = null;
            }
            $validated['audio_url'] = $request->file('file')->store('audios', 'public');
            $validated['summary'] = null;
            $validated['chapters'] = null;
            $fileChanged = true;
        } elseif ($request->boolean('delete_file') && $entry->audio_url) {
            if (! $entry->audio_is_external) {
                Storage::disk('public')->delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
            }
            $validated['audio_url'] = null;
            $validated['transcription_path'] = null;
            $validated['summary'] = null;
            $validated['chapters'] = null;
        } elseif ($request->has('audio_url') && $request->audio_url !== $entry->audio_url) {
            if ($entry->audio_url && ! $entry->audio_is_external) {
                Storage::disk('public')->delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
                $validated['transcription_path'] = null;
            }
            $validated['summary'] = null;
            $validated['chapters'] = null;
            $fileChanged = true;
        }

        if ($request->hasFile('image_file')) {
            Gate::authorize('uploadFiles', $entry);
            if ($entry->image_url && ! $entry->image_is_external) {
                Storage::disk('public')->delete($entry->image_url);
            }
            $validated['image_url'] = $request->file('image_file')->store('images', 'public');
        } elseif ($request->boolean('delete_image_file') && $entry->image_url) {
            if (! $entry->image_is_external) {
                Storage::disk('public')->delete($entry->image_url);
            }
            $validated['image_url'] = null;
        } elseif ($request->has('image_url') && $request->image_url !== $entry->image_url) {
            if ($entry->image_url && ! $entry->image_is_external) {
                Storage::disk('public')->delete($entry->image_url);
            }
        }

        unset($validated['delete_file']);
        unset($validated['delete_image_file']);

        $entry->update($validated);

        if ($fileChanged) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    public function destroy(Feed $feed, string $entry): RedirectResponse
    {
        $entry = Entry::where('slug', $entry)->where('feed_id', $feed->id)->firstOrFail();

        Gate::authorize('delete', $entry);

        if ($feed->rss_url) {
            abort(403, 'Entries in a synchronized feed cannot be deleted manually.');
        }

        if ($entry->audio_url && ! $entry->audio_is_external) {
            Storage::disk('public')->delete($entry->audio_url);
        }

        if ($entry->image_url && ! $entry->image_is_external) {
            Storage::disk('public')->delete($entry->image_url);
        }

        if ($entry->transcription_path) {
            Storage::delete($entry->transcription_path);
        }

        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    public function produce(Feed $feed, string $entry): RedirectResponse
    {
        $entry = Entry::where('slug', $entry)->where('feed_id', $feed->id)->firstOrFail();

        if ($entry->transcription_path) {
            Gate::authorize('regenerate', $entry);
        } else {
            Gate::authorize('produce', $entry);
        }

        $reuseTranscript = request()->boolean('reuse_transcript');

        if (! $reuseTranscript && ! $entry->audio_url) {
            abort(422, 'No audio file attached to this entry.');
        }

        if ($reuseTranscript && ! $entry->transcription_path) {
            abort(422, 'No transcription available to regenerate chapters and summary.');
        }

        if ($reuseTranscript) {
            $this->dispatchMetadataBatch($entry);
        } else {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', $reuseTranscript
            ? 'Chapters and summary regeneration queued successfully.'
            : 'Transcription queued successfully.');
    }
}
