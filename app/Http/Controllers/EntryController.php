<?php

namespace App\Http\Controllers;

use App\Concerns\DispatchesBatches;
use App\Concerns\LoadsFailedJobs;
use App\Facades\Media;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\Scopes\UserScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EntryController extends Controller
{
    use DispatchesBatches, LoadsFailedJobs;

    public function show(string $feed, string $entry): Response
    {
        $feed = Feed::withoutGlobalScope(UserScope::class)
            ->where('slug', $feed)
            ->firstOrFail();

        $entry = $feed->entries()
            ->where('slug', $entry)
            ->firstOrFail();

        Gate::authorize('view', $entry);

        $entry->setRelation('feed', $feed);
        $entry->load(['latestJobBatch']);

        $this->loadModelFailedJobDetails($entry);

        $user = auth()->user();

        $entry->can = [
            'produce' => $user?->can('produce', $entry) ?? false,
            'regenerate' => $user?->can('regenerate', $entry) ?? false,
        ];

        return Inertia::render('Entries/Show', [
            'entry' => $entry,
            'can' => [
                'update' => $user?->can('update', $entry) ?? false,
                'delete' => $user?->can('delete', $entry) ?? false,
                'uploadFiles' => $user?->can('uploadFiles', Entry::class) ?? false,
            ],
        ]);
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
            $validated['audio_url'] = Media::update('audios', null, $request->file('file'));
        }

        if ($request->hasFile('image_file')) {
            Gate::authorize('uploadFiles', Entry::class);
            $validated['image_url'] = Media::update('images', null, $request->file('image_file'));
        }

        $validated['slug'] = Entry::generateUniqueSlug($validated['name']);

        $entry = $feed->entries()->create($validated);

        if ($entry->audio_url) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(UpdateEntryRequest $request, Feed $feed, Entry $entry): RedirectResponse
    {
        Gate::authorize('update', $entry);

        if ($feed->rss_url) {
            abort(403, 'Entries in a synchronized feed cannot be modified manually.');
        }

        $validated = $request->validated();
        $fileChanged = false;

        if ($entry->shouldUpdateAudio($validated)) {
            if (isset($validated['file'])) {
                Gate::authorize('uploadFiles', $entry);
            }

            $validated['audio_url'] = Media::update(
                'audios',
                $entry->audio_url,
                $validated['file'] ?? $validated['audio_url'] ?? null,
                ! empty($validated['delete_file'])
            );

            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
                $validated['transcription_path'] = null;
            }

            $validated['summary'] = null;
            $validated['chapters'] = null;
            $fileChanged = empty($validated['delete_file']);
        }

        if ($entry->shouldUpdateImage($validated)) {
            if (isset($validated['image_file'])) {
                Gate::authorize('uploadFiles', $entry);
            }

            $validated['image_url'] = Media::update(
                'images',
                $entry->image_url,
                $validated['image_file'] ?? $validated['image_url'] ?? null,
                ! empty($validated['delete_image_file'])
            );
        }

        $entry->update(Arr::except($validated, ['file', 'delete_file', 'image_file', 'delete_image_file']));

        if ($fileChanged && $entry->audio_url) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    public function destroy(Feed $feed, Entry $entry): RedirectResponse
    {
        Gate::authorize('delete', $entry);

        if ($feed->rss_url) {
            abort(403, 'Entries in a synchronized feed cannot be deleted manually.');
        }

        Media::delete($entry->audio_url);
        Media::delete($entry->image_url);

        if ($entry->transcription_path) {
            Storage::delete($entry->transcription_path);
        }

        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    public function produce(Feed $feed, Entry $entry): RedirectResponse
    {
        Gate::authorize($entry->transcription_path ? 'regenerate' : 'produce', $entry);

        $reuseTranscript = request()->boolean('reuse_transcript');

        if (! $reuseTranscript && ! $entry->audio_url) {
            abort(422, 'No audio file attached to this entry.');
        }

        if ($reuseTranscript && ! $entry->transcription_path) {
            abort(422, 'No transcription available to regenerate chapters and summary.');
        }

        if ($reuseTranscript) {
            $this->dispatchMetadataBatch($entry);

            return redirect()->back()->with('success', 'Chapters and summary regeneration queued successfully.');
        }

        $this->dispatchTranscriptionBatch($entry);

        return redirect()->back()->with('success', 'Transcription queued successfully.');
    }
}
