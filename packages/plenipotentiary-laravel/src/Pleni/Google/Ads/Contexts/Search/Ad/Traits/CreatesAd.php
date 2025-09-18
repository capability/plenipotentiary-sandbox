<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO\AdDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO\AdExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait CreatesAd
{
    public function create(object $dto): object
    {
        \assert($dto instanceof AdDomainDTO);
        AdValidator::validateForCreate($dto);

        try {
            return new AdExternalDTO(null, null, $dto->status, $dto->headlines, $dto->descriptions, $dto->finalUrls, $dto->path1, $dto->path2, $dto->parentAdGroupResourceName);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error creating Ad");
        }
    }
}
