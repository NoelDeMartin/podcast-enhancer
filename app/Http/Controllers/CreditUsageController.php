<?php

namespace App\Http\Controllers;

use App\Concerns\LoadsFailedJobs;
use Illuminate\Http\JsonResponse;

class CreditUsageController extends Controller
{
    use LoadsFailedJobs;

    public function index(): JsonResponse
    {
        $user = request()->user();
        $usages = $user->creditUsages()
            ->with(['entry:id,name,slug,feed_id,transcription_path', 'entry.feed:id,slug', 'entry.latestJobBatch'])
            ->latest()
            ->paginate(10);

        $this->loadModelFailedJobDetails($usages->getCollection()->pluck('entry')->filter());

        return response()->json([
            'usages' => $usages,
            'current_credits' => $user->credits,
        ]);
    }
}
