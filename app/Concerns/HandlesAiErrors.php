<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;

trait HandlesAiErrors
{
    /**
     * Seconds to wait after each failed attempt. The last retry happens ~65 minutes after the
     * first attempt, giving some slack to rate limits that reset after one hour.
     *
     * @var array<int, int>
     */
    public const array AI_ERROR_BACKOFF = [60, 120, 240, 480, 960, 2040];

    protected function postponeIfRateLimited(RateLimitedException $exception, array $context = []): void
    {
        $backoff = $this->backoffForAttempt() ?? throw $exception;

        Log::info(static::class.' postponed due to AI provider rate limit.', array_merge([
            'attempt' => $this->attempts(),
            'error' => $exception->getMessage(),
        ], $context));

        $this->release($backoff);
    }

    protected function postponeIfOverloaded(ProviderOverloadedException $exception, array $context = []): void
    {
        $backoff = $this->backoffForAttempt() ?? throw $exception;

        Log::info(static::class.' postponed due to AI provider overload.', array_merge([
            'attempt' => $this->attempts(),
            'error' => $exception->getMessage(),
        ], $context));

        $this->release($backoff);
    }

    protected function backoffForAttempt(): ?int
    {
        return self::AI_ERROR_BACKOFF[$this->attempts() - 1] ?? null;
    }
}
