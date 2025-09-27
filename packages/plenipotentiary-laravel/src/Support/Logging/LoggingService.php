<?php

namespace Plenipotentiary\Laravel\Pleni\Support\Logging;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Log;

/**
 * Generic logging service wrapper.
 * Currently delegates to Laravel's Log facade,
 * but could be swapped with Loki/ELK/etc. via DI.
 */
class LoggingService implements LoggerInterface
{
    protected LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        // Use Laravel's default logger if none provided
        $this->logger = $logger ?? app(LoggerInterface::class);
    }

    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
