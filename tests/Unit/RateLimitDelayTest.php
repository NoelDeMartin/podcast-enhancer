<?php

use App\Ai\RateLimitDelay;
use Laravel\Ai\Exceptions\RateLimitedException;
use Prism\Prism\Exceptions\PrismRateLimitedException;

it('uses prism retryAfter when available', function () {
    $previous = new PrismRateLimitedException(rateLimits: [], retryAfter: 123);
    $exception = new RateLimitedException('rate limited', previous: $previous);

    expect(RateLimitDelay::forException($exception, 1))->toBe(123);
});

it('caps prism retryAfter to 3600 seconds', function () {
    $previous = new PrismRateLimitedException(rateLimits: [], retryAfter: 999_999);
    $exception = new RateLimitedException('rate limited', previous: $previous);

    expect(RateLimitDelay::forException($exception, 1))->toBe(3600);
});

it('falls back to exponential backoff when retryAfter is not available', function () {
    $exception = new RateLimitedException('rate limited');

    expect(RateLimitDelay::forException($exception, 1))->toBe(30);
    expect(RateLimitDelay::forException($exception, 2))->toBe(60);
    expect(RateLimitDelay::forException($exception, 3))->toBe(120);
    expect(RateLimitDelay::forException($exception, 10))->toBe(900);
});
