<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User;

use Plenipotentiary\Laravel\Contracts\CrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated\AdGroupCriterionService as GeneratedService;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate\AdGroupCriterionExternalToDomainMapper;

// todo: Add AdGroupCriterionRequestBuilder + AdGroupCriterionValidator in Support when request building/validation grows

/**
 * User-level AdGroupCriterionService (composition).
 * Implements CrudServiceContract and delegates to GeneratedService.
 */
class AdGroupCriterionService implements CrudServiceContract
{
    public function __construct(
        protected GeneratedService $generated
    ) {}

    public function create(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupCriterionDomainData);

        try {
            $external = $this->generated->createAdGroupCriterion($domainDto);

            return AdGroupCriterionExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, 'Error creating AdGroupCriterion');
        }
    }

    public function read(string|int $id): ?object
    {
        try {
            $external = $this->generated->getAdGroupCriterion((string) $id);

            return $external ? AdGroupCriterionExternalToDomainMapper::toDomain($external) : null;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading AdGroupCriterion '$id'");
        }
    }

    public function update(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupCriterionDomainData);
        try {
            $external = $this->generated->updateAdGroupCriterion($domainDto);

            return AdGroupCriterionExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating AdGroupCriterion '{$domainDto->resourceName}'");
        }
    }

    public function delete(string|int $id): bool
    {
        try {
            return $this->generated->removeAdGroupCriterion((string) $id);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error deleting AdGroupCriterion '$id'");
        }
    }

    public function listAll(array $criteria = []): iterable
    {
        // TODO: implement listAll for AdGroupCriterion
        return [];
    }
}
