<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\RateLimitedException;

trait HandlesAiRateLimits
{
    /**
     * Handle a rate limited exception by postponing the job.
     *
     * @throws RateLimitedException
     */
    protected function postponeIfRateLimited(RateLimitedException $exception, array $context = []): void
    {
        if ($this->attempts() >= 8) {
            throw $exception;
        }

        Log::info(static::class.' postponed due to AI provider rate limit. Retrying later...', array_merge([
            'attempt' => $this->attempts(),
        ], $context));

        $this->release([60, 120, 240, 480, 960, 1920, 3600][$this->attempts() - 1] ?? 3600);
    }
}
