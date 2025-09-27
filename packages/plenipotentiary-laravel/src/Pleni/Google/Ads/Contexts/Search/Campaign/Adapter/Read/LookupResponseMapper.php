<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Page;

final class LookupResponseMapper
{
    /** @return Page */
    public function toPage(object $resp): Page
    {
        // map rows → CampaignCanonical[], extract nextPageToken if available
        $items = [];
        foreach ($resp->iterateAllElements() as $row) {
            $c = new CampaignCanonicalDTO();
            $c->resourceName      = $row->getCampaign()->getResourceName();
            $c->id                = (string)$row->getCampaign()->getId();
            $c->name              = $row->getCampaign()->getName();
            $c->status            = $row->getCampaign()->getStatus();
            $c->budgetResourceName= $row->getCampaign()->getCampaignBudget();
            $items[] = $c;
        }
        $next = method_exists($resp, 'getNextPageToken') ? $resp->getNextPageToken() : null;
        return new Page($items, $next);
    }
}
