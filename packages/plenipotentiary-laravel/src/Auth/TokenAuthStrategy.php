<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

final class TokenAuthStrategy implements AuthStrategyContract
{
    public function __construct(
        private string $header = 'Authorization',
        private string $prefix = 'Bearer ',
        private string $value  = ''
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $token = $context['token'] ?? $this->value;
        if ($token === '') {
            return $request;
        }
        return $request->withHeader($this->header, $this->prefix.$token);
    }
}
