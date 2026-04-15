<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use App\Models\FailedJob;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EntryController extends Controller
{
    use DispatchesBatches;

    public function show(Feed $feed, Entry $entry): Response
    {
        if ($entry->feed_id !== $feed->id) {
            abort(404);
        }

        $entry->load('feed', 'latestJobBatch');

        $this->loadFailedJobDetails($entry);

        $entry->transcription = $entry->transcription_path ? Storage::get($entry->transcription_path) : null;

        return Inertia::render('Entries/Show', [
            'entry' => $entry,
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

    public function store(StoreEntryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $feed = Feed::findOrFail($validated['feed_id']);
        if ($feed->rss_url) {
            abort(403, 'Manual entries cannot be added to a synchronized feed.');
        }

        if ($request->hasFile('file')) {
            $validated['audio_url'] = $request->file('file')->store('entries');
        }

        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $request->file('image_file')->store('images', 'public');
        }

        $entry = Entry::create($validated);

        if ($entry->audio_url) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(UpdateEntryRequest $request, Entry $entry): RedirectResponse
    {
        if ($entry->feed->rss_url) {
            abort(403, 'Entries in a synchronized feed cannot be modified manually.');
        }

        $validated = $request->validated();

        $fileChanged = false;

        if ($request->hasFile('file')) {
            if ($entry->audio_url && ! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
                Storage::delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
                $validated['transcription_path'] = null;
            }
            $validated['audio_url'] = $request->file('file')->store('entries');
            $validated['summary'] = null;
            $validated['chapters'] = null;
            $fileChanged = true;
        } elseif ($request->boolean('delete_file') && $entry->audio_url) {
            if (! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
                Storage::delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
            }
            $validated['audio_url'] = null;
            $validated['transcription_path'] = null;
            $validated['summary'] = null;
            $validated['chapters'] = null;
        } elseif ($request->has('audio_url') && $request->audio_url !== $entry->audio_url) {
            if ($entry->audio_url && ! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
                Storage::delete($entry->audio_url);
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
            if ($entry->image_url && ! filter_var($entry->image_url, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($entry->image_url);
            }
            $validated['image_url'] = $request->file('image_file')->store('images', 'public');
        } elseif ($request->boolean('delete_image_file') && $entry->image_url) {
            if (! filter_var($entry->image_url, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($entry->image_url);
            }
            $validated['image_url'] = null;
        } elseif ($request->has('image_url') && $request->image_url !== $entry->image_url) {
            if ($entry->image_url && ! filter_var($entry->image_url, FILTER_VALIDATE_URL)) {
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

    public function destroy(Entry $entry): RedirectResponse
    {
        if ($entry->feed->rss_url) {
            abort(403, 'Entries in a synchronized feed cannot be deleted manually.');
        }

        if ($entry->audio_url && ! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
            Storage::delete($entry->audio_url);
        }

        if ($entry->image_url && ! filter_var($entry->image_url, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($entry->image_url);
        }

        if ($entry->transcription_path) {
            Storage::delete($entry->transcription_path);
        }

        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    public function produce(Request $request, Entry $entry): RedirectResponse
    {
        $reuseTranscript = $request->boolean('reuse_transcript');

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

    public function file(Entry $entry)
    {
        if (! $entry->audio_url) {
            abort(404);
        }

        if (filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
            return redirect()->away($entry->audio_url);
        }

        return Storage::response($entry->audio_url);
    }
}
