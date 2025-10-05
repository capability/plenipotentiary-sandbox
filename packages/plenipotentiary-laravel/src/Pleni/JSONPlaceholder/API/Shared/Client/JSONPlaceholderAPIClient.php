<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Client;

use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Transfer\Rest\JSONPlaceholderAPIRestConnector;

/**
 * Wraps the Saloon Connector to implement ProviderClientContract.
 * This allows the same adapter signature for both SDK and REST integrations.
 */
final class JSONPlaceholderAPIClient implements ProviderClientContract
{
    public function __construct(
        private JSONPlaceholderAPIRestConnector $connector
    ) {}

    public function raw(): object
    {
        return $this->connector;
    }
}
