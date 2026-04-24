<?php

namespace App\Concerns;

use App\Models\FailedJob;
use App\Models\JobBatch;
use Illuminate\Support\Collection;

trait LoadsFailedJobs
{
    protected function loadFailedJobDetails(Collection|JobBatch $batches): void
    {
        $batches = Collection::wrap($batches);

        $failedJobIds = $batches
            ->flatMap(fn (JobBatch $batch) => $batch->failed_job_ids ?? [])
            ->unique()
            ->values()
            ->all();

        if (empty($failedJobIds)) {
            return;
        }

        $failedJobs = FailedJob::whereIn('uuid', $failedJobIds)->get()->keyBy('uuid');

        $batches->each(function (JobBatch $batch) use ($failedJobs) {
            $batch->setRelation(
                'failedJobDetails',
                collect($batch->failed_job_ids ?? [])
                    ->map(fn (string $uuid) => $failedJobs->get($uuid))
                    ->filter()
                    ->values(),
            );
        });
    }
}
