<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Transfer\Rest;

use Saloon\Http\Connector;

final class JSONPlaceholderAPIRestConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://jsonplaceholder.typicode.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
