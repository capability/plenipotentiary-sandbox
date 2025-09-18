<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User;

use Plenipotentiary\Laravel\Contracts\ApiCrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated\AdService as GeneratedService;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate\AdExternalToDomainMapper;

/**
 * Thin AdService implementing ApiCrudServiceContract.
 * Delegates raw API operations to GeneratedService.
 */
class AdService implements ApiCrudServiceContract
{
    public function __construct(
        protected GeneratedService $generated
    ) {}

    public function create(object $domainDto): object
    {
        \assert($domainDto instanceof AdDomainData);

        try {
            $external = $this->generated->createAd($domainDto);

            return AdExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, 'Error creating Ad');
        }
    }

    public function read(string|int $id): ?object
    {
        try {
            $external = $this->generated->getAd((string) $id);

            return $external ? AdExternalToDomainMapper::toDomain($external) : null;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading Ad '$id'");
        }
    }

    public function update(object $domainDto): object
    {
        \assert($domainDto instanceof AdDomainData);
        try {
            $external = $this->generated->updateAd($domainDto);

            return AdExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating Ad '{$domainDto->resourceName}'");
        }
    }

    public function delete(string|int $id): bool
    {
        try {
            return $this->generated->removeAd((string) $id);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error deleting Ad '$id'");
        }
    }

    public function listAll(array $criteria = []): iterable
    {
        // TODO: implement listAll for Ad
        return [];
    }
}
