<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails;

use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests\GetItemRequest;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Gateway for the GetItemDetails operation.
 *
 * Provides a stable interface for retrieving detailed information about
 * specific eBay items.
 */
final class GetItemDetailsGateway
{
    public function __construct(
        private readonly RestGatewayContract $restGateway,
    ) {}

    /**
     * Execute a get item details operation.
     *
     * @param  GetItemDetailsOperation  $operation  The operation to execute
     * @return Result<array, array> Success contains item details, error contains failure info
     */
    public function execute(GetItemDetailsOperation $operation): Result
    {
        $request = new GetItemRequest(
            itemId: $operation->itemId,
            fieldgroups: $operation->fieldgroups,
        );

        return $this->restGateway->execute($request);
    }

    /**
     * Convenience: Get item by ID with default fields.
     */
    public function getById(string $itemId): Result
    {
        return $this->execute(new GetItemDetailsOperation($itemId));
    }

    /**
     * Convenience: Get compact item details.
     */
    public function getCompact(string $itemId): Result
    {
        return $this->execute(GetItemDetailsOperation::compact($itemId));
    }

    /**
     * Convenience: Get item with product information.
     */
    public function getWithProduct(string $itemId): Result
    {
        return $this->execute(GetItemDetailsOperation::withProduct($itemId));
    }
}
