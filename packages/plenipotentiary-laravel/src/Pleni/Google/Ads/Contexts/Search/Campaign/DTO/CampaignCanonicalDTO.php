<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

final class CampaignCanonicalDTO
{
    /** @var array<string,string> */
    public array   $accountKeys       = [];    // provider account identifiers (google.customerId, fb.adAccountId, etc.)

    public ?string $internalId        = null;  // our own system id
    public ?string $externalId        = null;  // provider id (Google resourceName, FB id, etc.)
    /** @var array<string,string> */
    public array   $identifiers       = [];    // loose bag for provider extras

    public ?string $name               = null;
    public ?string $status             = null; // e.g. ENABLED|PAUSED
    public ?string $budgetResourceName = null; // customers/{cid}/campaignBudgets/{bid}
    public ?int    $cpcBidMicros       = null;
    public ?int    $budgetMicros       = null; // optional, used when creating budget on the fly

    public static function fromArray(array $data): self
    {
        $c = new self();
        $c->accountKeys        = $data['accountKeys'] ?? ['google.customerId' => env('GOOGLE_ADS_LINKED_CUSTOMER_ID', '')];
        $c->internalId         = $data['internalId'] ?? null;
        $c->externalId         = $data['externalId'] ?? null;
        $c->identifiers        = $data['identifiers'] ?? [];
        $c->name               = $data['name']               ?? null;
        $c->status             = $data['status']             ?? null;
        $c->budgetResourceName = $data['budgetResourceName'] ?? null;
        $c->cpcBidMicros       = isset($data['cpcBidMicros']) ? (int) $data['cpcBidMicros'] : null;
        $c->budgetMicros       = isset($data['budgetMicros']) ? (int) $data['budgetMicros'] : null;
        return $c;
    }

    public function externalId(): ?string { return $this->externalId; }
    public function internalId(): ?string { return $this->internalId; }
    public function identifier(string $key): ?string { return $this->identifiers[$key] ?? null; }

    public function toArray(): array
    {
        return [
            'accountKeys'        => $this->accountKeys,
            'internalId'         => $this->internalId,
            'externalId'         => $this->externalId,
            'identifiers'        => $this->identifiers,
            'name'               => $this->name,
            'status'             => $this->status,
            'budgetResourceName' => $this->budgetResourceName,
            'cpcBidMicros'       => $this->cpcBidMicros,
            'budgetMicros'       => $this->budgetMicros,
        ];
    }
}
