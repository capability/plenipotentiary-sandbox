<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO\AdExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait ReadsAd
{
    public function read(string|int $id): ?object
    {
        try {
            return null; // placeholder
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading Ad '$id'");
        }
    }

    public function listAll(array $criteria = []): iterable
    {
        return [];
    }
}
