<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Check if a product is compatible with a specified item.
 * 
 * This request is primarily used for automotive parts and accessories
 * to determine if an item is compatible with a specific vehicle.
 * 
 * You provide the item ID and compatibility properties (like year, make, model)
 * to check if the item fits or works with the specified product.
 * 
 * @see https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/checkCompatibility
 */
final class CheckCompatibilityRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param string $itemId The unique RESTful identifier of the item to check
     * @param array $compatibilityProperties Array of compatibility properties
     *                                        Example: [
     *                                            ['name' => 'Year', 'value' => '2019'],
     *                                            ['name' => 'Make', 'value' => 'Toyota'],
     *                                            ['name' => 'Model', 'value' => 'Camry'],
     *                                        ]
     */
    public function __construct(
        private readonly string $itemId,
        private readonly array $compatibilityProperties,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/buy/browse/v1/item/{$this->itemId}/check_compatibility";
    }

    protected function defaultBody(): array
    {
        return [
            'compatibilityProperties' => $this->compatibilityProperties,
        ];
    }
}
