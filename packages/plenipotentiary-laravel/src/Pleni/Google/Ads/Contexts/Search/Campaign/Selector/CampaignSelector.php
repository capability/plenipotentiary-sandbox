<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector;

use Plenipotentiary\Laravel\Contracts\Selector\SelectorContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;

final class CampaignSelector implements SelectorContract
{
    /** @var array<string,string> */
    private array $providerContext;

    private function __construct(
        private readonly string $type,
        private readonly string $value,
        array $providerContext = []
    ) {
        $this->providerContext = $this->normaliseContext($providerContext);
    }

    public static function make(string $type, string $value, array $providerContext = []): self
    {
        return new self($type, $value, $providerContext);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function providerContext(): array
    {
        return $this->providerContext;
    }

    public function getProviderContextValue(string $key): ?string
    {
        return $this->providerContext[$key] ?? null;
    }

    public function toCanonicalDTO(): CampaignCanonicalDTO
    {
        $payload = ['providerContext' => $this->providerContext];

        if ($this->type === 'external_id') {
            $payload['externalId'] = $this->value;
        } elseif ($this->type === 'local_id') {
            $payload['internalId'] = $this->value;
        }

        return CampaignCanonicalDTO::fromArray($payload);
    }

    private function normaliseContext(array $context): array
    {
        $filtered = array_filter($context, static fn ($value) => $value !== null && $value !== '');

        return GoogleAdsDefaults::apply($filtered);
    }
}
