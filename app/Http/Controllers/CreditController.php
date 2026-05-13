<?php

namespace App\Http\Controllers;

use App\Concerns\LoadsFailedJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    use LoadsFailedJobs;

    public function index(): JsonResponse
    {
        $user = request()->user();

        $usagesQuery = $user->creditUsages()
            ->select([
                'id',
                'credits',
                'created_at',
                'entry_id',
                DB::raw("'usage' as type"),
                DB::raw('NULL as description'),
            ]);

        $topUpsQuery = $user->creditTopUps()
            ->select([
                'id',
                'credits',
                'created_at',
                DB::raw('NULL as entry_id'),
                DB::raw("'topup' as type"),
                'description',
            ]);

        $usages = $usagesQuery->unionAll($topUpsQuery)
            ->orderByDesc('created_at')
            ->paginate(8);

        $usageItems = $usages->getCollection()->where('type', 'usage');

        $usageItems->load([
            'entry:id,name,slug,feed_id,transcription_path',
            'entry.feed:id,slug',
            'entry.latestJobBatch',
        ]);

        $this->loadModelFailedJobDetails($usageItems->pluck('entry')->filter());

        return response()->json([
            'usages' => $usages,
            'current_credits' => $user->credits,
        ]);
    }
}
