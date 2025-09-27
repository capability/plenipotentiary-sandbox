<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget;

use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V21\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

/**
 * Builds CampaignBudgetOperation for unified mutate requests.
 */
final class RequestMapper
{
    /**
     * @param int $tempId negative temporary id (e.g. -1)
     */
    public function toBudgetOperation(CampaignCanonicalDTO $c, int $tempId): CampaignBudgetOperation
    {
        $budget = new CampaignBudget([
            'name'         => $c->name . ' Budget',
            'amount_micros'=> $c->budgetMicros ?? 1000000,
            'delivery_method' => BudgetDeliveryMethod::STANDARD,
            'resource_name'   => sprintf('customers/%s/campaignBudgets/%d', $c->accountKeys['google.customerId'] ?? $c->customerId ?? '', $tempId),
        ]);

        return (new CampaignBudgetOperation())->setCreate($budget);
    }
}
