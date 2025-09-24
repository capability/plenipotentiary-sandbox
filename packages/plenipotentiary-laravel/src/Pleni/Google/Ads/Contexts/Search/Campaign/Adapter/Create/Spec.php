<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create;

use Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Pleni\Support\Operation\OperationDescription;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Contracts\Adapter\SpecContract;

/**
 * Thin preflight, provider-agnostic. Real business validation happens via validateOnly on the API.
 */
final class Spec implements SpecContract
{
    public function preflight(CampaignCanonicalDTO $c): void
    {
        $violations = [];

        if (!$c->name || mb_strlen($c->name) > 128) {
            $violations[] = ['field' => 'name', 'rule' => 'required|string|max:128', 'mapsTo' => 'campaign.name'];
        }

        if (!in_array($c->status, ['ENABLED', 'PAUSED'], true)) {
            $violations[] = ['field' => 'status', 'rule' => 'enum[ENABLED,PAUSED]', 'mapsTo' => 'campaign.status'];
        }

        if (!$c->budgetResourceName) {
            $violations[] = ['field' => 'budgetResourceName', 'rule' => 'required resource_name', 'mapsTo' => 'campaign.campaign_budget'];
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
            ['field' => 'budgetResourceName', 'rule' => 'required resource_name', 'mapsTo' => 'campaign.campaign_budget'],
        ]);
    }
}
