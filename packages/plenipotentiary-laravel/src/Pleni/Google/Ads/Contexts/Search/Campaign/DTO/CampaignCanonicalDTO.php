<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;

final class CampaignCanonicalDTO implements CanonicalDTOContract
{
    /** @var array<string,string> */
    public array $providerContext = [];

    public ?string $internalId = null;

    public ?string $externalId = null;

    public ?string $name = null;

    public ?string $status = null;

    public ?string $budgetResourceName = null;

    public ?int $cpcBidMicros = null;

    public ?int $budgetMicros = null;

    /**
     * Defines the canonical shape expected by factories/controllers when hydrating this DTO.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function schema(): array
    {
        return [
            'internalId' => ['key' => 'internal_id'],
            'externalId' => ['key' => 'external_id'],
            'name' => ['key' => 'name'],
            'status' => ['key' => 'status'],
            'budgetResourceName' => ['key' => 'budget_resource_name'],
            'budgetMicros' => ['key' => 'budget', 'cast' => 'currency_to_micros'],
            'cpcBidMicros' => ['key' => 'cpc_bid', 'cast' => 'int'],
        ];
    }

    public static function fromArray(array $data): self
    {
        $dto = new self;
        $dto->providerContext = self::filterContext($data['providerContext'] ?? $data['accountKeys'] ?? []);
        $dto->internalId = $data['internalId'] ?? null;
        $dto->externalId = $data['externalId'] ?? null;
        $dto->name = $data['name'] ?? null;
        $dto->status = $data['status'] ?? null;
        $dto->budgetResourceName = $data['budgetResourceName'] ?? null;
        $dto->cpcBidMicros = isset($data['cpcBidMicros']) ? (int) $data['cpcBidMicros'] : null;
        $dto->budgetMicros = isset($data['budgetMicros']) ? (int) $data['budgetMicros'] : null;

        return $dto;
    }

    public function setProviderContext(array $context): void
    {
        $this->providerContext = self::filterContext($context);
    }

    public function mergeProviderContext(array $context): void
    {
        $this->providerContext = self::filterContext(array_merge($this->providerContext, $context));
    }

    public function getProviderContextValue(string $key): ?string
    {
        return $this->providerContext[$key] ?? null;
    }

    public function toArray(): array
    {
        return [
            'providerContext' => $this->providerContext,
            'internalId' => $this->internalId,
            'externalId' => $this->externalId,
            'name' => $this->name,
            'status' => $this->status,
            'budgetResourceName' => $this->budgetResourceName,
            'cpcBidMicros' => $this->cpcBidMicros,
            'budgetMicros' => $this->budgetMicros,
        ];
    }

    private static function filterContext(array $context): array
    {
        return array_filter(
            $context,
            static fn ($value) => $value !== null && $value !== ''
        );
    }
}
