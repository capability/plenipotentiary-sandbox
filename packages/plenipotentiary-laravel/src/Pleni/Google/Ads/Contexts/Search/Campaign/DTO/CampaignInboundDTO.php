<?php

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

readonly class CampaignInboundDTO implements InboundDTOContract
{
    public function __construct(
        public string $id,
        public string $resourceName,
        public string $status,
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : '',
            resourceName: isset($data['resourceName']) ? (string) $data['resourceName'] : '',
            status: isset($data['status'])
                ? (is_string($data['status']) ? $data['status'] : (string) $data['status'])
                : '',
            name: $data['name'] ?? null,
        );
    }
}
