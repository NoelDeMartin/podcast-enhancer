<?php

namespace App\Concerns;

use App\Models\FailedJob;
use App\Models\Feed;
use App\Models\JobBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait LoadsFailedJobs
{
    protected function loadFailedJobDetails(Collection|JobBatch|null $batches): void
    {
        if (! $batches) {
            return;
        }

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

    protected function loadModelFailedJobDetails(Model|Collection $models): void
    {
        $batches = Collection::wrap($models)
            ->flatMap(function ($model) {
                $batches = collect();

                if ($model->relationLoaded('latestJobBatch') && $model->latestJobBatch?->jobBatch) {
                    $batches->push($model->latestJobBatch->jobBatch);
                }

                if ($model instanceof Feed && $model->relationLoaded('entries')) {
                    $model->entries->each(function ($entry) use ($batches) {
                        if ($entry->relationLoaded('latestJobBatch') && $entry->latestJobBatch?->jobBatch) {
                            $batches->push($entry->latestJobBatch->jobBatch);
                        }
                    });
                }

                return $batches;
            })
            ->filter();

        $this->loadFailedJobDetails($batches);
    }
}
