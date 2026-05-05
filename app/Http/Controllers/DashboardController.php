<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'feeds' => Feed::withCount('entries')
                ->filter(request()->only('search'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => request()->only(['search']),
            'can' => [
                'uploadFiles' => request()->user()?->can('uploadFiles', Feed::class) ?? false,
            ],
        ]);
    }
}
