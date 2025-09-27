<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelectorKind;

final class CampaignSelector
{
    public function __construct(
        private CampaignSelectorKind $kind,
        private string $value,
        private ?string $customerId = null
    ) {}

    public static function byResourceName(string $rn, ?string $customerId = null): self
    {
        return new self(CampaignSelectorKind::ResourceName, $rn, $customerId);
    }

    public static function byId(string $id, ?string $customerId = null): self
    {
        return new self(CampaignSelectorKind::ExternalId, $id, $customerId);
    }

    public function kind(): CampaignSelectorKind { return $this->kind; }

    public function value(): string { return $this->value; }

    public function customerId(): string
    {
        return $this->customerId ?: (string) env('GOOGLE_ADS_LINKED_CUSTOMER_ID', '');
    }

    public function toResourceName(?string $customerId = null): string
    {
        $cid = $customerId ?: $this->customerId();
        return match ($this->kind) {
            CampaignSelectorKind::ResourceName => $this->value,
            CampaignSelectorKind::ExternalId   => sprintf('customers/%s/campaigns/%s', $cid, $this->value),
            CampaignSelectorKind::LocalId      => sprintf('customers/%s/campaigns/%s', $cid, $this->value),
        };
    }
}
