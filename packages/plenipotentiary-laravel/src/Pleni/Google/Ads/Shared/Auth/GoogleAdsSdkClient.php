<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsHelper;

final class GoogleAdsSdkClient implements ProviderClientContract
{
    public function __construct(private GoogleAdsClient $client)
    {
    }

    /**
     * Expose the authenticated GoogleAdsClient for adapters
     */
    public function getClient(): GoogleAdsClient
    {
        return $this->client;
    }

    public function getLoginCustomerId(): string
    {
        // Retrieve from helper to avoid leaking SDK-specific details
        return GoogleAdsHelper::loginCustomerId();
    }

    /**
     * Return the raw underlying GoogleAdsClient instance.
     */
    public function raw(): object
    {
        return $this->client;
    }
}
