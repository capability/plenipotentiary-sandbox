<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Adapter;

use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\DTO\CustomerCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Selector\CustomerSelector;
use Plenipotentiary\Laravel\Support\Result;

/**
 * CRUD Adapter for Stripe Customers.
 *
 * This demonstrates that the CRUD adapter pattern works perfectly
 * with REST APIs using Saloon, not just SDKs. Each operation uses
 * Saloon to make HTTP calls to Stripe's REST API.
 *
 * Note: This uses a different contract than CrudAdapterContract
 * because Stripe doesn't have a "lookup" concept like Google Ads does.
 */
final class CustomerCrudAdapter
{
    public function __construct(
        private CustomerCreate $createOperation,
        private CustomerUpdate $updateOperation,
        private CustomerDelete $deleteOperation,
        private CustomerRead $readOperation,
    ) {}

    /**
     * Create a customer via REST API.
     */
    public function create(CustomerCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->createOperation->perform($dto, $validateOnly);
    }

    /**
     * Update a customer via REST API.
     */
    public function update(CustomerCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->updateOperation->perform($dto, $validateOnly);
    }

    /**
     * Find a single customer by ID via REST API.
     */
    public function find(CustomerSelector $sel): Result
    {
        return $this->readOperation->perform($sel->toCanonicalDTO());
    }

    /**
     * Delete a customer via REST API.
     */
    public function delete(CustomerSelector $sel, bool $validateOnly = false): Result
    {
        return $this->deleteOperation->perform($sel->toCanonicalDTO(), $validateOnly);
    }
}
