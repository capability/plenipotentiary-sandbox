<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create;

use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

final class RequestMapper implements CreateRequestMapperContract
{
    public function toCampaignsRequest(CampaignCanonicalDTO $c, bool $validateOnly = false): MutateCampaignsRequest
    {
        $customerId = $this->resolveCustomerId($c);
        $campaignOperation = $this->buildCampaignOperation($c, $c->budgetResourceName ?? '');

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$campaignOperation])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public function toUnifiedRequest(
        CampaignCanonicalDTO $c,
        bool $validateOnly,
        CampaignBudgetOperation $budgetOp
    ): MutateGoogleAdsRequest {
        $customerId = $this->resolveCustomerId($c);
        $budgetResourceName = $budgetOp->getCreate()?->getResourceName();

        if ($budgetResourceName === null) {
            throw new \InvalidArgumentException('Budget operation must include a resource name when creating unified request.');
        }

        $campaignOperation = $this->buildCampaignOperation($c, $budgetResourceName);

        $mutateOperations = [
            (new MutateOperation())->setCampaignBudgetOperation($budgetOp),
            (new MutateOperation())->setCampaignOperation($campaignOperation),
        ];

        return (new MutateGoogleAdsRequest)
            ->setCustomerId($customerId)
            ->setMutateOperations($mutateOperations)
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    private function buildCampaignOperation(CampaignCanonicalDTO $c, string $budgetResourceName): CampaignOperation
    {
        $status = $c->status ? CampaignStatus::value($c->status) : CampaignStatus::ENABLED;

        $campaign = new Campaign([
            'name' => $c->name,
            'status' => $status,
            'campaign_budget' => $budgetResourceName,
        ]);

        return (new CampaignOperation())->setCreate($campaign);
    }

    private function resolveCustomerId(CampaignCanonicalDTO $c): string
    {
        $customerId = $c->providerContext['google.customerId'] ?? null;

        if ($customerId === null || $customerId === '') {
            throw new \InvalidArgumentException('Campaign DTO missing google.customerId account key');
        }

        return (string) $customerId;
    }
}
