<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Example Saloon Request for searching campaigns in Google Ads API.
 * 
 * This demonstrates the REST/Saloon pattern where each API endpoint
 * is represented by its own self-contained Request class.
 */
final class SearchCampaignsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $customerId,
        private readonly string $query,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/customers/{$this->customerId}/googleAds:search";
    }

    protected function defaultBody(): array
    {
        return [
            'query' => $this->query,
        ];
    }
}
