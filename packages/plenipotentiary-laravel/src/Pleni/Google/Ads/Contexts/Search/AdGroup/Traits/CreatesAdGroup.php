<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO\AdGroupDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO\AdGroupExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdGroupValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait CreatesAdGroup
{
    public function create(object $dto): object
    {
        \assert($dto instanceof AdGroupDomainDTO);
        AdGroupValidator::validateForCreate($dto);

        try {
            // Placeholder: wire into Google Ads Generated Service
            return new AdGroupExternalDTO(null, null, $dto->name, $dto->status, $dto->campaignResourceName, $dto->maxCpc);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error creating AdGroup '{$dto->name}'");
        }
    }
}
