<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Procedure;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Dynamic Saloon request for eBay Browse Procedure/RPC operations.
 *
 * This class allows creating requests on-the-fly without dedicated
 * request classes for each endpoint.
 */
final class eBayBrowseDynamicRequest extends Request implements HasBody
{
    use HasJsonBody;

    public function __construct(
        private readonly Method $method,
        private readonly string $endpoint,
        private readonly array $body = [],
        private readonly array $query = [],
        private readonly array $headers = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    protected function defaultHeaders(): array
    {
        return $this->headers;
    }

    protected function defaultQuery(): array
    {
        return $this->query;
    }

    protected function defaultBody(): array
    {
        return $this->body;
    }

    protected function getMethod(): Method
    {
        return $this->method;
    }
}
