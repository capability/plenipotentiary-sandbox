<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Client;

use Psr\Http\Message\ResponseInterface;

/**
 * Abstraction for HTTP-based provider clients.
 */
interface HttpProviderClientContract extends ProviderClientContract
{
    /**
     * Perform an HTTP request against the provider.
     *
     * @param  string  $method   HTTP method
     * @param  string  $endpoint Path (or full URL if client is not using a base_uri)
     * @param  array   $options  Transport-level options (headers, query, json, multipart, timeout, etc.)
     */
    public function request(string $method, string $endpoint, array $options = []): ResponseInterface;
}