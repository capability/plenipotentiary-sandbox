<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport;

use Google\Ads\GoogleAds\V21\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Google\Ads\GoogleAds\V21\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignBudgetsRequest;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

/**
 * Small helper that encapsulates the provider-specific budget creation call used by CreateOperation.
 *
 * Keeping this logic here makes CreateOperation easier to scan while still exposing the
 * exact Google Ads SDK usage we rely on when a caller does not provide an existing budget.
 */
class CreateBudgetOperation
{
    public function __construct(private ProviderClientContract $client) {}

    public function create(CampaignCanonicalDTO $source, int $budgetMicros, bool $validateOnly = false): string
    {
        $budget = new CampaignBudget([
            'name' => sprintf('%s Budget', trim($source->name ?? 'Campaign')),
            'amount_micros' => $budgetMicros,
            'delivery_method' => BudgetDeliveryMethod::STANDARD,
        ]);

        $operation = (new CampaignBudgetOperation)->setCreate($budget);

        $request = (new MutateCampaignBudgetsRequest)
            ->setCustomerId($source->getProviderContextValue('google.customerId'))
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly);

        $response = $this->client
            ->raw()
            ->getCampaignBudgetServiceClient()
            ->mutateCampaignBudgets($request);

        if ($validateOnly) {
            // Google Ads does not return resource names during validate-only calls; provide a
            // deterministic placeholder so the downstream campaign request can still reference it.
            return sprintf('customers/%s/campaignBudgets/validate-only-temp', $source->getProviderContextValue('google.customerId'));
        }

        return (string) ($response->getResults()[0]?->getResourceName() ?? '');
    }
}
