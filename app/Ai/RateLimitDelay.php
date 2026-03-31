<?php

namespace App\Ai;

use Laravel\Ai\Exceptions\RateLimitedException;
use Prism\Prism\Exceptions\PrismRateLimitedException;

class RateLimitDelay
{
    public static function forException(RateLimitedException $exception, int $attempts = 1): int
    {
        $previous = $exception->getPrevious();

        if ($previous instanceof PrismRateLimitedException && $previous->retryAfter !== null) {
            return max(1, min(3600, $previous->retryAfter));
        }

        // Exponential backoff with a reasonable cap to avoid hammering the provider.
        $attempt = max(1, $attempts);
        $delay = 30 * (2 ** min($attempt - 1, 6)); // 30s, 60s, 120s, ... capped

        return (int) min(900, $delay);
    }
}
