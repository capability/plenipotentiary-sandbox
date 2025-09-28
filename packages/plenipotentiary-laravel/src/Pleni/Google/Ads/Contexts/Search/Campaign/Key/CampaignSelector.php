<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key;

final class CampaignSelector
{
	/** @var array<string,string> provider account identifiers (e.g. google.customerId) */
	private array $accountKeys;

	private function __construct(
		private CampaignSelectorKind $kind,
		private string $value,
		array $accountKeys = []
	) {
		$this->accountKeys = $accountKeys ?: ['google.customerId' => env('GOOGLE_ADS_LINKED_CUSTOMER_ID', '')];
	}

	public static function byResourceName(string $resourceName, array $accountKeys = []): self
	{
		return new self(CampaignSelectorKind::ResourceName, $resourceName, $accountKeys);
	}

	public static function byExternalId(string $id, array $accountKeys = []): self
	{
		return new self(CampaignSelectorKind::ExternalId, $id, $accountKeys);
	}

	public static function byLocalId(string $id, array $accountKeys = []): self
	{
		return new self(CampaignSelectorKind::LocalId, $id, $accountKeys);
	}

	public function kind(): CampaignSelectorKind { return $this->kind; }
	public function value(): string { return $this->value; }

	/** Convenience for the common key; remains provider-aware in Google namespace */
	public function customerId(): ?string
	{
		return $this->accountKeys['google.customerId'] ?? null;
	}

	/** Access entire bag for multi-provider consistency */
	public function accountKeys(): array
	{
		return $this->accountKeys;
	}

	/** GA-specific: build a resource_name when kind != ResourceName */
	public function resourceName(?string $overrideCustomerId = null): string
	{
		$cid = $overrideCustomerId ?: $this->customerId() ?: '';
		return match ($this->kind) {
			CampaignSelectorKind::ResourceName => $this->value,
			CampaignSelectorKind::ExternalId,
			CampaignSelectorKind::LocalId => sprintf('customers/%s/campaigns/%s', $cid, $this->value),
		};
	}

	/** Build a minimal canonical DTO skeleton for status-only updates, etc. */
	public function toCanonicalSkeleton(): \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO
	{
		return \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO::fromArray([
			'accountKeys' => $this->accountKeys,
			'externalId'  => $this->kind === CampaignSelectorKind::ExternalId ? $this->value : null,
			'identifiers' => [
				'resourceName' => $this->resourceName(),
			],
		]);
	}
}
