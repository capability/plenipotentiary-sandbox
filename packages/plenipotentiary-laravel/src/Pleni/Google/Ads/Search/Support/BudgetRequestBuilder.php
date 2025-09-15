<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\V20\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Google\Ads\GoogleAds\V20\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V20\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V20\Services\MutateCampaignBudgetsRequest;

/**
 * Builder utility for shared budget mutate requests.
 */
class BudgetRequestBuilder
{
    public static function buildCreate(string $customerId, string $name, float $dailyBudget): MutateCampaignBudgetsRequest
    {
        $budget = new CampaignBudget([
            'name' => $name,
            'amount_micros' => GoogleAdsUtil::toMicros($dailyBudget),
            'delivery_method' => BudgetDeliveryMethod::STANDARD,
            'explicitly_shared' => true,
        ]);

        $operation = new CampaignBudgetOperation;
        $operation->setCreate($budget);

        return (new MutateCampaignBudgetsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation]);
    }
}
