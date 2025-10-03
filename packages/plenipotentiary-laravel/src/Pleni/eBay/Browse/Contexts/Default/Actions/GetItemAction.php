<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions;

use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails\GetItemDetailsDTO;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails\GetItemDetailsGateway;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails\GetItemDetailsOperation;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Action for retrieving eBay item details using the Operation pattern.
 *
 * This action retrieves full details of a specific eBay item.
 * Use this when you need comprehensive information about an item
 * including description, pricing, seller info, shipping options, etc.
 */
final class GetItemAction
{
    public function __construct(
        private readonly GetItemDetailsGateway $gateway,
    ) {}

    /**
     * Get full item details using a pre-built operation.
     *
     * @param  GetItemDetailsOperation  $operation  The operation to execute
     * @return Result<GetItemDetailsDTO, array> Success contains item details DTO
     */
    public function execute(GetItemDetailsOperation $operation): Result
    {
        $result = $this->gateway->execute($operation);

        // Transform successful results into a DTO
        if ($result->isOk()) {
            $data = $result->unwrap();
            $dto = GetItemDetailsDTO::fromApiResponse($data);

            return Result::ok($dto);
        }

        return $result;
    }

    /**
     * Get full item details by item ID (convenience method).
     *
     * @param  string  $itemId  The eBay item ID (format: v1|legacyId|variationId)
     * @param  array  $options  Additional options (see GetItemDetailsOperation)
     */
    public function getById(string $itemId, array $options = []): Result
    {
        $operation = GetItemDetailsOperation::fromArray([
            'itemId' => $itemId,
            ...$options,
        ]);

        return $this->execute($operation);
    }

    /**
     * Get compact item details for change detection.
     *
     * This is useful for checking if item availability, price,
     * or status has changed since last check.
     */
    public function getCompact(string $itemId): Result
    {
        return $this->execute(GetItemDetailsOperation::compact($itemId));
    }

    /**
     * Get item with product information.
     *
     * Includes additional product-level details.
     */
    public function getWithProduct(string $itemId): Result
    {
        return $this->execute(GetItemDetailsOperation::withProduct($itemId));
    }
}
