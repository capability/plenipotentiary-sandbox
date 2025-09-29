<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Page;

final class LookupResponseMapper
{
    public function toPage(object $resp): Page
    {
        $items = [];
        foreach ($resp->iterateAllElements() as $row) {
            $c = new CampaignCanonicalDTO;
            $c->resourceName = $row->getCampaign()->getResourceName();
            $c->id = (string) $row->getCampaign()->getId();
            $c->name = $row->getCampaign()->getName();
            $c->status = $row->getCampaign()->getStatus();
            $c->budgetResourceName = $row->getCampaign()->getCampaignBudget();
            $items[] = $c;
        }
        $next = method_exists($resp, 'getNextPageToken') ? $resp->getNextPageToken() : null;

        return new Page($items, $next);
    }
}