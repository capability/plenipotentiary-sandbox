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

    public ?string $customerId = null;

    /**
     * Defines the canonical shape expected by factories/controllers when hydrating this DTO.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function schema(): array
    {
        return [
            'internalId' => ['key' => 'internal_id', 'rules' => ['nullable', 'string']],
            'externalId' => ['key' => 'external_id', 'rules' => ['nullable', 'string']],
            'name' => ['key' => 'name', 'rules' => ['required', 'string', 'min:1', 'max:128']],
            'status' => ['key' => 'status', 'rules' => ['required', 'in:ENABLED,PAUSED,REMOVED']],
            'budgetResourceName' => ['key' => 'budget_resource_name', 'rules' => ['nullable', 'string']],
            'budgetMicros' => ['key' => 'budget', 'rules' => ['nullable', 'numeric', 'min:0'], 'cast' => 'currency_to_micros'],
            'cpcBidMicros' => ['key' => 'cpc_bid', 'rules' => ['nullable', 'numeric', 'min:0'], 'cast' => 'int'],
            'customerId' => ['key' => 'customer_id', 'rules' => ['nullable', 'string']],
        ];
    }

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
        $c->customerId = $data['customerId'] ?? null;

        if ($c->customerId) {
            $c->providerContext['google.customerId'] = $c->customerId;
        } elseif (isset($c->providerContext['google.customerId'])) {
            $c->customerId = $c->providerContext['google.customerId'];
        }

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
        $merged = array_filter(
            array_merge($this->providerContext, $context),
            fn ($value) => $value !== null && $value !== ''
        );

        $this->providerContext = $merged;

        if (isset($merged['google.customerId'])) {
            $this->customerId = $merged['google.customerId'];
        }
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
            'customerId' => $this->customerId,
        ];
    }
}
