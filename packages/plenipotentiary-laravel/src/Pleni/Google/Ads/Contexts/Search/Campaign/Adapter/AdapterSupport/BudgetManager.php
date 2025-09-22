<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport;

use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Google\Ads\GoogleAds\V20\Services\{
    CampaignBudgetOperation,
    MutateCampaignBudgetsRequest
};
use Google\Ads\GoogleAds\V20\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V20\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;

final class BudgetManager
{
    public function __construct(
        private ProviderClientContract $client,
    ) {}

    /**
     * Ensure budget exists or create one.
     */
    public function ensureSharedBudget(OutboundDTOContract $dto): string
    {
        $budgetName = 'Shared Budget for Search Campaigns';

        // Create budget operation
        $budget = (new CampaignBudget())
            ->setName($budgetName)
            ->setAmountMicros($dto->budgetMicros)
            ->setDeliveryMethod(BudgetDeliveryMethod::STANDARD);

        $budgetOperation = (new CampaignBudgetOperation())->setCreate($budget);

        $request = (new MutateCampaignBudgetsRequest())
            ->setCustomerId($dto->customerId ?? '')
            ->setOperations([$budgetOperation]);

        $gaClient = $this->client->raw();
        $response = $gaClient->getCampaignBudgetServiceClient()->mutateCampaignBudgets($request);

        return $response->getResults()[0]->getResourceName();
    }
}
