<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Throwable;

/**
 * Generic queued job to dispatch service + method + payload.
 * Keeps plenipotentiary queuing generic and reusable for any API edge.
 */
class PlenipotentiaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $serviceClass,
        protected string $method,
        protected array $payload
    ) {}

    public function handle(): void
    {
        try {
            $service = App::make($this->serviceClass);

            if (! method_exists($service, $this->method)) {
                throw new \RuntimeException("Method {$this->serviceClass}::{$this->method} does not exist");
            }

            // If payload has a DTO factory we can hydrate here.
            // For now, we pass payload directly to method.
            $service->{$this->method}(...array_values($this->payload));
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }
}
