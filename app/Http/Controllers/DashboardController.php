<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'feeds' => Feed::withCount('entries')->latest()->get(),
        ]);
    }
}
