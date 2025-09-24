<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete;

use Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Pleni\Support\Operation\OperationDescription;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Contracts\Adapter\SpecContract;

final class Spec implements SpecContract
{
    public function preflight(CampaignSelector $sel): void
    {
        $violations = [];
        if (!$sel->value()) {
            $violations[] = ['field' => 'selector', 'rule' => 'required', 'mapsTo' => 'campaign.resource_name or id'];
        }
        if ($violations) {
            throw ValidationException::fromArray('campaign.delete', $violations);
        }
    }

    public function describe(): OperationDescription
    {
        return OperationDescription::make('campaign.delete', [
            ['field' => 'selector', 'rule' => 'required', 'mapsTo' => 'campaign.resource_name or id'],
        ]);
    }
}
