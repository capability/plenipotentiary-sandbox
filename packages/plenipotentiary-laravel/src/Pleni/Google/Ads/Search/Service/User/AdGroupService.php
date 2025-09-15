<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User;

use Plenipotentiary\Laravel\Contracts\CrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated\AdGroupService as GeneratedService;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate\AdGroupExternalToDomainMapper;

// todo: Add AdGroupRequestBuilder + AdGroupValidator support classes when request building/validation grows

/**
 * User-level AdGroupService (composition).
 * Implements CrudServiceContract and delegates to GeneratedService.
 */
class AdGroupService implements CrudServiceContract
{
    public function __construct(
        protected GeneratedService $generated
    ) {}

    public function create(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupDomainData);

        try {
            $external = $this->generated->createAdGroup($domainDto);

            return AdGroupExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error creating AdGroup '{$domainDto->name}'");
        }
    }

    public function read(string|int $id): ?object
    {
        try {
            $external = $this->generated->getAdGroup((string) $id);

            return $external ? AdGroupExternalToDomainMapper::toDomain($external) : null;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading AdGroup '$id'");
        }
    }

    public function update(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupDomainData);

        try {
            $external = $this->generated->updateAdGroup($domainDto);

            return AdGroupExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating AdGroup '{$domainDto->resourceName}'");
        }
    }

    public function delete(string|int $id): bool
    {
        try {
            return $this->generated->removeAdGroup((string) $id);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error deleting AdGroup '$id'");
        }
    }

    public function listAll(array $criteria = []): iterable
    {
        // TODO: implement listAll for AdGroup
        return [];
    }
}
