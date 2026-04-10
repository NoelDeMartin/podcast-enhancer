<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedRssController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Feed $feed): Response
    {
        $entries = $feed->entries;

        return response()
            ->view('feeds.rss', compact('feed', 'entries'))
            ->header('Content-Type', 'text/xml');
    }
}
