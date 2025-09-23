<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignInboundDTO;

/**
 * Contract for adapter-side mappers that translate provider SDK responses
 * into canonical CampaignInboundDTO objects.
 */
interface InboundDTOMapperContract
{
    /**
     * Map from a mutate response (add/update/remove) into DTOs.
     *
     * @param object|iterable $response
     * @param string $operation
     * @param string|null $requestId
     * @return CampaignInboundDTO[]
     */
    public static function fromMutateResponse(object|iterable $response, string $operation, ?string $requestId = null): array;

    /**
     * Build a DTO from a single provider search/get row.
     *
     * @param object $row
     * @param string|null $requestId
     */
    public static function fromSearchRow(object $row, ?string $requestId = null): CampaignInboundDTO;

    /**
     * Convert an exception into a non-throwing DTO.
     *
     * @param object $ex
     */
    public static function fromException(object $ex): CampaignInboundDTO;
}
