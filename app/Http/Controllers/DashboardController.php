<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $filters = request()->only('search');

        $feeds = Feed::withCount('entries')
            ->with(['latestJobBatch'])
            ->filter($filters)
            ->latest()
            ->get();

        $feeds->each(fn (Feed $feed) => $feed->setAttribute('can', [
            'update' => $user->can('update', $feed),
            'delete' => $user->can('delete', $feed),
            'sync' => $user->can('sync', $feed),
        ]));

        return Inertia::render('Dashboard/Index', [
            'feeds' => $feeds,
            'filters' => $filters,
            'can' => [
                'createManual' => $user->can('createManual', Feed::class),
                'uploadFiles' => $user->can('uploadFiles', Feed::class),
            ],
        ]);
    }
}
