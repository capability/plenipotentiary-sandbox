<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateResponseMapperContract;

final class ResponseMapper implements CreateResponseMapperContract
{
    public function toCanonical(MutateCampaignsResponse $resp): CampaignCanonicalDTO
    {
        // With MUTABLE_RESOURCE you get the created Campaign back on results[0]->getCampaign()
        $result   = $resp->getResults()[0] ?? null;
        $resource = $result?->getCampaign();
        $c = new CampaignCanonicalDTO();

        if ($resource) {
            $c->resourceName       = $resource->getResourceName();
            $c->id                 = (string) $resource->getId();
            $c->name               = $resource->getName();
            $c->status             = $resource->getStatus();
            $c->budgetResourceName = $resource->getCampaignBudget();
        } else {
            // Fallback to resource name only
            $c->resourceName = $result?->getResourceName();
        }

        return $c;
    }
}
