<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Illuminate\Support\Facades\Log;
use Plenipotentiary\Laravel\Pleni\Jobs\PlenipotentiaryJob;
use Throwable;

/**
 * Generic, composable helpers for Generated services.
 * Provides retry, logging, and monitoring hooks that can be used by any integration.
 */
class GeneratedServiceHelpers
{
    /**
     * Run a callable with configurable retries and backoff.
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public static function withRetries(callable $operation, int $maxAttempts = 3, int $backoffMs = 200)
    {
        $attempts = 0;
        beginning:
        try {
            $attempts++;

            return $operation();
        } catch (Throwable $e) {
            if ($attempts >= $maxAttempts) {
                throw $e;
            }
            usleep($backoffMs * 1000);
            goto beginning;
        }
    }

    /**
     * Run and log an operation (duration + errors).
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public static function withLogging(string $context, array $meta, callable $operation)
    {
        $start = microtime(true);

        try {
            $result = $operation();
            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::info("[GeneratedService] {$context} succeeded in {$duration}ms", $meta);

            return $result;
        } catch (Throwable $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::error("[GeneratedService] {$context} failed after {$duration}ms: {$e->getMessage()}", $meta);
            throw $e;
        }
    }

    /**
     * Enqueue an operation instead of running it immediately.
     */
    public static function withQueue(string $serviceClass, string $method, array $payload = []): void
    {
        // This generic job is provided inside plenipotentiary core
        // It can be dispatched via Laravel queues
        PlenipotentiaryJob::dispatch($serviceClass, $method, $payload);
    }

    /**
     * Wrap an operation and emit metrics (duration, success/failure).
     *
     * @throws Throwable
     */
    public static function withMetrics(string $metric, callable $operation): mixed
    {
        $start = microtime(true);
        try {
            $result = $operation();
            $duration = round((microtime(true) - $start) * 1000, 2);

            // simplistic monitoring; could be swapped for Prometheus, StatsD, etc.
            Log::info("[Metrics] {$metric} succeeded in {$duration}ms");

            return $result;
        } catch (Throwable $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::error("[Metrics] {$metric} failed after {$duration}ms: {$e->getMessage()}");
            throw $e;
        }
    }
}
