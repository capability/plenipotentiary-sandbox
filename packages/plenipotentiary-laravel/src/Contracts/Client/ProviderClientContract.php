<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Client;

/**
 * Abstraction for any provider client (HTTP client or SDK client).
 */
interface ProviderClientContract
{
    /**
     * Return the raw underlying client object (SDK or HTTP).
     */
    public function raw(): object;
}
