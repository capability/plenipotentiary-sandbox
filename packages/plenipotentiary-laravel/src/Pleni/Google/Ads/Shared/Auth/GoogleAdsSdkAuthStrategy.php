<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth;

use Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V21\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Psr\Http\Message\RequestInterface;

/**
 * A concrete SdkAuthStrategyContract implementation for Google Ads.
 *
 * This leverages environment variables (Google Ads credentials) to construct
 * and return an authenticated GoogleAdsClient. Adapters and gateways can then
 * obtain the authenticated client by calling getClient().
 */
final class GoogleAdsSdkAuthStrategy implements SdkAuthStrategyContract
{
    private GoogleAdsClient $client;

    public function __construct()
    {
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->withClientId(env('GOOGLE_ADS_CLIENT_ID'))
            ->withClientSecret(env('GOOGLE_ADS_CLIENT_SECRET'))
            ->withRefreshToken(env('GOOGLE_ADS_REFRESH_TOKEN'))
            ->build();

        $this->client = (new GoogleAdsClientBuilder())
            ->withOAuth2Credential($oAuth2Credential)
            ->withDeveloperToken(env('GOOGLE_ADS_DEVELOPER_TOKEN'))
            ->withLoginCustomerId((int) env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'))
            ->build();
    }

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // For SDK-based clients, authentication is handled internally,
        // so this is effectively a no-op passthrough.
        return $request;
    }

    /**
     * Return the authenticated GoogleAdsClient instance.
     *
     * @return GoogleAdsClient
     */
    public function getClient(): object
    {
        return $this->client;
    }
}
