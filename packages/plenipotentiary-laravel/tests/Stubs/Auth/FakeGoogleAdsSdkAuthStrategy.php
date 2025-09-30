<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Tests\Stubs\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Psr\Http\Message\RequestInterface;

final class FakeGoogleAdsSdkAuthStrategy implements SdkAuthStrategyContract
{
    public function __construct(private object $client) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request;
    }

    public function getClient(): object
    {
        return $this->client;
    }
}
