<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO\AdGroupExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait ReadsAdGroup
{
    public function read(string|int $id): ?object
    {
        try {
            // Placeholder: query via Finder
            return null;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading AdGroup '$id'");
        }
    }

    public function listAll(array $criteria = []): iterable
    {
        return [];
    }
}
