<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector;

use Plenipotentiary\Laravel\Contracts\Selector\SelectorContract;

final class CampaignSelector implements SelectorContract
{
    /** @var array<string,string> provider context hints (e.g. google.customerId, resourceName) */
    private array $providerContext;

    private function __construct(
        private CampaignSelectorKind $kind,
        private string $value,
        array $providerContext = []
    ) {
        $this->providerContext = array_filter($providerContext, fn ($value) => $value !== null && $value !== '');
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

    /** Access entire bag for multi-provider consistency */
    public function providerContext(): array
    {
        return $this->providerContext;
    }
}
