<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Policies;

use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicy;
use Plenipotentiary\Laravel\Support\Logging\Redactor;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class LoggingPolicy implements GatewayPolicy
{
    public function __construct(
        private LoggerInterface $logger,
        private Redactor $redactor = new Redactor
    ) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $this->logger->info('gateway.call.start', [
            'operation' => $call->operation,
            'context' => $call->context,
        ]);

        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        $this->logger->info('gateway.call.ok', [
            'operation' => $call->operation,
            'meta' => $result->toArray(),
        ]);

        return $result;
    }

    public function onError(GatewayCall $call, \Throwable|Result $error): Result
    {
        $this->logger->error('gateway.call.error', [
            'operation' => $call->operation,
            'error' => $error instanceof Result ? $error->toArray() : ['exception' => $error::class, 'message' => $error->getMessage()],
        ]);

        return $error instanceof Result ? $error : Result::err($error);
    }
}
