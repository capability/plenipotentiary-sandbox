<?php

namespace Plenipotentiary\Laravel\Pleni\Support\Logging;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class LoggingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoggerInterface::class, function ($app) {
            return new LoggingService();
        });
    }
}
