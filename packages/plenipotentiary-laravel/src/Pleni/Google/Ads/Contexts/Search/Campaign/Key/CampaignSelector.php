<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key;

use Plenipotentiary\Laravel\Contracts\Selector\SelectorContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;

final class CampaignSelector implements SelectorContract
{
    /** @var array<string,string> provider context hints (e.g. google.customerId, resourceName) */
    private array $providerContext;

    private function __construct(
        private CampaignSelectorKind $kind,
        private string $value,
        array $providerContext = []
    ) {
        $this->providerContext = GoogleAdsDefaults::hydrate($providerContext);

        if ($this->kind === CampaignSelectorKind::ResourceName && ! isset($this->providerContext['resourceName'])) {
            $this->providerContext['resourceName'] = $this->value;
        }
    }

    public static function byResourceName(string $resourceName, array $providerContext = []): self
    {
        return new self(CampaignSelectorKind::ResourceName, $resourceName, $providerContext);
    }

    public static function byExternalId(string $id, array $providerContext = []): self
    {
        return new self(CampaignSelectorKind::ExternalId, $id, $providerContext);
    }

    public static function byLocalId(string $id, array $providerContext = []): self
    {
        return new self(CampaignSelectorKind::LocalId, $id, $providerContext);
    }

    public function kind(): CampaignSelectorKind
    {
        return $this->kind;
    }

    public function type(): string
    {
        return $this->kind->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    /** Convenience for the common key; remains provider-aware in Google namespace */
    public function customerId(): ?string
    {
        return $this->providerContext['google.customerId'] ?? GoogleAdsDefaults::get('google.customerId');
    }

    /** Access entire bag for multi-provider consistency */
    public function providerContext(): array
    {
        return $this->providerContext;
    }

}
