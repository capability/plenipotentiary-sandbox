<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create;

use Plenipotentiary\Laravel\Contracts\Adapter\SpecContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\Operation\OperationDescription;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;

/**
 * Thin preflight, provider-agnostic. Real business validation happens via validateOnly on the API.
 */
final class Spec implements SpecContract
{
    public function preflight(mixed $input): void
    {
        if (! $input instanceof CampaignCanonicalDTO) {
            throw new \InvalidArgumentException('Create campaign spec expects a CampaignCanonicalDTO instance.');
        }

        $c = $input;
        $violations = [];

        if (! $c->name || mb_strlen($c->name) > 128) {
            $violations[] = ['field' => 'name', 'rule' => 'required|string|max:128', 'mapsTo' => 'campaign.name'];
        }

        if (! in_array($c->status, ['ENABLED', 'PAUSED'], true)) {
            $violations[] = ['field' => 'status', 'rule' => 'enum[ENABLED,PAUSED]', 'mapsTo' => 'campaign.status'];
        }

        if (! $c->budgetResourceName && $c->budgetMicros === null) {
            $violations[] = ['field' => 'budgetResourceName', 'rule' => 'required unless budgetMicros provided', 'mapsTo' => 'campaign.campaign_budget'];
        }

        if ($violations) {
            throw ValidationException::fromArray('campaign.create', $violations);
        }
    }

    public function describe(): OperationDescription
    {
        return OperationDescription::make('campaign.create', [
            ['field' => 'name', 'rule' => 'required|string|max:128', 'mapsTo' => 'campaign.name'],
            ['field' => 'status', 'rule' => 'enum[ENABLED,PAUSED]', 'mapsTo' => 'campaign.status'],
            ['field' => 'budgetResourceName', 'rule' => 'required unless budgetMicros provided', 'mapsTo' => 'campaign.campaign_budget'],
        ]);
    }
}
