<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;

final class CampaignCanonicalDTO
{
    /** @var array<string,string> */
    public array $providerContext = []; // provider hints (google.customerId, resourceName, etc.)

    public ?string $internalId = null;  // our own system id

    public ?string $externalId = null;  // provider id (Google resourceName, FB id, etc.)

    /** @var array<string,string> */
    public array $identifiers = [];    // loose bag for provider extras

    public ?string $name = null;

    public ?string $status = null; // e.g. ENABLED|PAUSED

    public ?string $budgetResourceName = null; // customers/{cid}/campaignBudgets/{bid}

    public ?int $cpcBidMicros = null;

    public ?int $budgetMicros = null; // optional, used when creating budget on the fly

    public static function fromArray(array $data): self
    {
        $c = new self;
        $context = $data['providerContext'] ?? $data['accountKeys'] ?? [];
        $c->providerContext = array_filter($context, fn ($value) => $value !== null && $value !== '');
        $c->internalId = $data['internalId'] ?? null;
        $c->externalId = $data['externalId'] ?? null;
        $c->identifiers = $data['identifiers'] ?? [];
        $c->name = $data['name'] ?? null;
        $c->status = $data['status'] ?? null;
        $c->budgetResourceName = $data['budgetResourceName'] ?? null;
        $c->cpcBidMicros = isset($data['cpcBidMicros']) ? (int) $data['cpcBidMicros'] : null;
        $c->budgetMicros = isset($data['budgetMicros']) ? (int) $data['budgetMicros'] : null;

        return $c;
    }

    public function externalId(): ?string
    {
        return $this->externalId;
    }

    public function internalId(): ?string
    {
        return $this->internalId;
    }

    public function identifier(string $key): ?string
    {
        return $this->identifiers[$key] ?? null;
    }

    public function providerContextValue(string $key): ?string
    {
        $context = GoogleAdsDefaults::apply($this->providerContext);

        return $context[$key] ?? null;
    }

    public function providerContext(): array
    {
        return $this->providerContext;
    }

    public function mergeProviderContext(array $context): void
    {
        $this->providerContext = array_filter(
            array_merge($this->providerContext, $context),
            fn ($value) => $value !== null && $value !== ''
        );
    }

    public function customerId(): ?string
    {
        return $this->providerContextValue('google.customerId');
    }

    public function resourceName(): ?string
    {
        return $this->identifier('resourceName') ?? $this->providerContextValue('resourceName');
    }

    public function toArray(): array
    {
        return [
            'providerContext' => $this->providerContext,
            'internalId' => $this->internalId,
            'externalId' => $this->externalId,
            'identifiers' => $this->identifiers,
            'name' => $this->name,
            'status' => $this->status,
            'budgetResourceName' => $this->budgetResourceName,
            'cpcBidMicros' => $this->cpcBidMicros,
            'budgetMicros' => $this->budgetMicros,
        ];
    }
}
