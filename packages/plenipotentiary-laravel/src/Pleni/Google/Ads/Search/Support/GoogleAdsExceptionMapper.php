<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsException;
use Google\ApiCore\ApiException;

/**
 * Maps raw Google Ads API exceptions into domain-specific exceptions.
 */
class GoogleAdsExceptionMapper extends ExceptionMapper
{
    /**
     * Map any exception from the SDK into a domain exception.
     */
    public static function map(\Throwable $e, string $contextMessage): \Throwable
    {
        if ($e instanceof GoogleAdsException) {
            $messages = [];
            foreach ($e->getGoogleAdsFailure()->getErrors() as $error) {
                $messages[] = sprintf(
                    "Error with message '%s' and code '%s'.",
                    $error->getMessage(),
                    $error->getErrorCode()->getErrorCode()
                );
            }

            return new GoogleAdsApiException($contextMessage."\n".implode("\n", $messages), 0, $e);
        }

        if ($e instanceof ApiException) {
            return new GoogleAdsTransportException($contextMessage.' ApiException: '.$e->getMessage(), 0, $e);
        }

        return $e;
    }
}

/**
 * Base domain exception for Google Ads integration.
 */
class GoogleAdsIntegrationException extends \RuntimeException {}

/**
 * Exception for API-level (business logic) errors.
 */
class GoogleAdsApiException extends GoogleAdsIntegrationException {}

/**
 * Exception for transport-level errors (network, gRPC, etc).
 */
class GoogleAdsTransportException extends GoogleAdsIntegrationException {}
