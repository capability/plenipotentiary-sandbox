<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Error;

interface ErrorMapperContract
{
    /**
     * Map a thrown exception from an SDK or API client into a
     * package-specific exception hierarchy.
     *
     * Adapters should typically map low-level SDK exceptions into
     * package-specific ones, while gateways should rely on this mapper
     * to expose only generic categories:
     *  - TransportException
     *  - AuthException
     *  - DomainException
     *  - ValidationException
     *
     * This ensures internal SDK/provider details do not leak through
     * the gateway boundary.
     *
     * @throws \Plenipotentiary\Laravel\Exceptions\TransportException
     * @throws \Plenipotentiary\Laravel\Exceptions\AuthException
     * @throws \Plenipotentiary\Laravel\Exceptions\DomainException
     * @throws \Plenipotentiary\Laravel\Exceptions\ValidationException
     */
    public function map(\Throwable $e): \Throwable;
}
