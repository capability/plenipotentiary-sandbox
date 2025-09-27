<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update;

use Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Pleni\Support\Operation\OperationDescription;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Contracts\Adapter\SpecContract;

final class Spec implements SpecContract
{
    public function preflight(CampaignCanonicalDTO $c): void
    {
        $violations = [];

        if (!$c->resourceName) {
            $violations[] = ['field' => 'resourceName', 'rule' => 'required', 'mapsTo' => 'campaign.resource_name'];
        }

        if (!$c->name && !$c->status) {
            $violations[] = ['field' => '(name|status)', 'rule' => 'at least one updatable field required', 'mapsTo' => 'campaign'];
        }

        if ($violations) {
            throw ValidationException::fromArray('campaign.update', $violations);
        }
    }

    public function describe(): OperationDescription
    {
        return OperationDescription::make('campaign.update', [
            ['field' => 'resourceName', 'rule' => 'required', 'mapsTo' => 'campaign.resource_name'],
            ['field' => 'name', 'rule' => 'optional|string|max:128', 'mapsTo' => 'campaign.name'],
            ['field' => 'status', 'rule' => 'optional|enum[ENABLED,PAUSED]', 'mapsTo' => 'campaign.status'],
        ]);
    }
}
