<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Support;

use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;

final class CampaignIdempotencyHints implements IdempotencyHints
{
    public function fingerprintForCreate(CampaignCanonicalDTO $campaign): string
    {
        $parts = [
            'create',
            $campaign->getProviderContextValue('resourceName') ?? '',
            $campaign->externalId ?? '',
            $campaign->internalId ?? '',
            $campaign->getProviderContextValue('google.customerId') ?? '',
            $campaign->name ?? '',
        ];

        return hash('sha256', implode('|', $parts));
    }

    public function fingerprintForUpdate(CampaignCanonicalDTO $campaign): string
    {
        $parts = [
            'update',
            $campaign->getProviderContextValue('resourceName')
                ?? $campaign->externalId
                ?? $campaign->internalId
                ?? '',
            $campaign->status ?? '',
            (string) ($campaign->budgetMicros ?? ''),
        ];

        return hash('sha256', implode('|', $parts));
    }

    public function fingerprintForDelete(CampaignSelector $selector): string
    {
        $parts = [
            'delete',
            $selector->type(),
            $selector->value(),
            $selector->getProviderContextValue('google.customerId') ?? '',
            $selector->getProviderContextValue('resourceName') ?? '',
        ];

        return hash('sha256', implode('|', $parts));
    }
}
