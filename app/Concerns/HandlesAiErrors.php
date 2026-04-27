<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;

trait HandlesAiErrors
{
    protected function postponeIfRateLimited(RateLimitedException $exception, array $context = []): void
    {
        if ($this->attempts() >= 8) {
            throw $exception;
        }

        Log::info(static::class.' postponed due to AI provider rate limit.', array_merge([
            'attempt' => $this->attempts(),
        ], $context));

        $this->release($this->backoffForAttempt());
    }

    protected function postponeIfOverloaded(ProviderOverloadedException $exception, array $context = []): void
    {
        if ($this->attempts() >= 8) {
            throw $exception;
        }

        Log::info(static::class.' postponed due to AI provider overload.', array_merge([
            'attempt' => $this->attempts(),
        ], $context));

        $this->release($this->backoffForAttempt());
    }

    protected function backoffForAttempt(): int
    {
        return [60, 120, 240, 480, 960, 1920, 3600][$this->attempts() - 1] ?? 3600;
    }
}
