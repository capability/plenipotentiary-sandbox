<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\ContextualInboundDTOContract;

/**
 * Canonical, provider-agnostic inbound DTO for a Campaign.
 */
final class CampaignInboundDTO implements InboundDTOContract, ContextualInboundDTOContract
{
    private string $resource = 'campaign';
    private string $operation = 'unknown';
    private ?string $externalResourceId = null;
    private ?string $externalResourceLabel = null;
    private string $externalResourceStatus = 'unknown';
    private array $meta = [];
    private array $attributes = [];
    private array $warnings = [];
    private array $rawResponse = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    public function getResource(): string { return $this->resource; }
    public function getOperation(): string { return $this->operation; }
    public function getExternalResourceId(): ?string { return $this->externalResourceId; }
    public function getExternalResourceLabel(): ?string { return $this->externalResourceLabel; }
    public function getExternalResourceStatus(): string { return $this->externalResourceStatus; }
    public function getMeta(): array { return $this->meta; }
    public function getAttributes(): array { return $this->attributes; }
    public function getWarnings(): array { return $this->warnings; }
    public function getRawResponse(): array|string|null { return $this->rawResponse; }

    public function toArray(): array
    {
        return [
            'resource'              => $this->resource,
            'operation'             => $this->operation,
            'externalResourceId'    => $this->externalResourceId,
            'externalResourceLabel' => $this->externalResourceLabel,
            'externalResourceStatus'=> $this->externalResourceStatus,
            'meta'                  => $this->meta,
            'attributes'            => $this->attributes,
            'warnings'              => $this->warnings,
            'rawResponse'           => $this->rawResponse,
        ];
    }

    public function withOperation(string $op): self { $this->operation = $op; return $this; }
    public function withExternalResourceId(?string $id): self { $this->externalResourceId = $id; return $this; }
    public function withExternalResourceLabel(?string $label): self { $this->externalResourceLabel = $label; return $this; }
    public function withExternalResourceStatus(string $status): self { $this->externalResourceStatus = $status; return $this; }
    public function withMeta(array $meta): self { $this->meta = $meta; return $this; }
    public function withAttributes(array $attrs): self { $this->attributes = $attrs; return $this; }
    public function withWarnings(array $warnings): self { $this->warnings = $warnings; return $this; }
    public function withRawResponse(array $raw): self { $this->rawResponse = $raw; return $this; }
}
