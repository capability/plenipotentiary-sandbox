<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport;
 
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignInboundDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport\InboundDTOMapperContract;

/**
 * Factory/mapper for transforming Google Ads SDK responses into
 * canonical CampaignInboundDTO objects.
 *
 * This isolates Google Ads SDK knowledge from the DTO itself.
 */
final class CampaignApiInboundDTOMapper implements InboundDTOMapperContract
{
    /**
     * Build DTOs from a mutate response (add/update/remove).
     *
     * @param object|iterable $response
     * @param string $operation
     * @param string|null $requestId
     * @return CampaignInboundDTO[]
     */
    public static function fromMutateResponse(object|iterable $response, string $operation, ?string $requestId = null): array
    {
        $dtos = [];
        foreach (self::extractResults($response) as $result) {
            $resourceName = self::call($result, 'getResourceName');

            $dtos[] = new CampaignInboundDTO([
                'operation'             => $operation,
                'meta'                  => ['requestId' => $requestId],
                'attributes'            => [
                    'google_ads.ads.resource_name' => $resourceName,
                ],
                'rawResponse'           => ['resourceName' => $resourceName],
            ]);
        }
        return $dtos;
    }

    /**
     * Build a DTO from a single GoogleAdsRow (search/get).
     */
    public static function fromSearchRow(object $row, ?string $requestId = null): CampaignInboundDTO
    {
        $campaign = self::call($row, 'getCampaign');
        return self::fromCampaignObject($campaign, $requestId);
    }

    /**
     * Build a DTO directly from a Campaign object.
     */
    public static function fromCampaignObject(object $campaign, ?string $requestId = null): CampaignInboundDTO
    {
        $id      = self::call($campaign, 'getId');
        $name    = self::call($campaign, 'getName');
        $status  = self::call($campaign, 'getStatus');
        $channel = self::call($campaign, 'getAdvertisingChannelType');
        $rn      = self::call($campaign, 'getResourceName');

        $raw = array_filter([
            'resourceName' => $rn,
            'id'           => $id,
            'name'         => $name,
            'status'       => $status,
            'channel'      => $channel,
        ]);

        // If logging flag is set, include full serialized campaign object (redacted)
        if (env('PLENI_LOG_FULL_RAW', false) && method_exists($campaign, 'serializeToJsonString')) {
            $raw['full'] = \Plenipotentiary\Laravel\Support\Logging\Redactor::body(
                json_decode($campaign->serializeToJsonString(), true) ?? []
            );
        }

        return new CampaignInboundDTO([
            'operation'             => 'get',
            'externalResourceId'    => $id !== null ? (string)$id : null,
            'externalResourceLabel' => $name ?: null,
            'externalResourceStatus'=> self::mapStatusToState($status),
            'meta'                  => ['requestId' => $requestId],
            'attributes' => array_filter([
                'google_ads.ads.resource_name'            => $rn,
                'google_ads.ads.advertising_channel_type' => $channel,
                'google_ads.ads.status_raw'               => $status,
            ]),
            'rawResponse'           => $raw,
        ]);
    }

    /**
     * Convert exception into a non-throwing DTO.
     */
    public static function fromException(object $ex): CampaignInboundDTO
    {
        $requestId = self::call($ex, 'getRequestId');
        $warnings  = [];

        $failure = self::call($ex, 'getGoogleAdsFailure');
        if ($failure && method_exists($failure, 'getErrors')) {
            foreach ($failure->getErrors() as $err) {
                $codeObj = self::call($err, 'getErrorCode');
                $code    = (is_object($codeObj) && method_exists($codeObj, 'getErrorCode'))
                    ? $codeObj->getErrorCode()
                    : null;
                $msg = self::call($err, 'getMessage') ?? 'Unknown error';
                $warnings[] = trim(($code ? $code . ': ' : '') . $msg);
            }
        } elseif (method_exists($ex, 'getMessage')) {
            $warnings[] = $ex->getMessage();
        }

        return new CampaignInboundDTO([
            'operation'             => 'unknown',
            'meta'                  => ['requestId' => $requestId],
            'warnings'              => $warnings,
            'attributes'            => [],
            'rawResponse'           => ['exceptionClass' => get_class($ex)],
        ]);
    }

    /* ----------------------- helpers ----------------------- */

    private static function extractResults(object|iterable $response): iterable
    {
        if (is_iterable($response)) return $response;

        if (method_exists($response, 'getResults')) {
            $results = $response->getResults();
            if (is_iterable($results)) return $results;
        }
        if (property_exists($response, 'results') && is_iterable($response->results)) {
            return $response->results;
        }
        return [];
    }

    private static function call(?object $obj, string $method): mixed
    {
        return $obj && method_exists($obj, $method) ? $obj->$method() : null;
    }

    private static function mapStatusToState(mixed $status): string
    {
        return match ((string)$status) {
            'ENABLED'     => 'active',
            'PAUSED'      => 'paused',
            'REMOVED'     => 'deleted',
            'UNKNOWN', '' => 'unknown',
            default       => 'unknown',
        };
    }
}
