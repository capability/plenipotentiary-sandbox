<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Auth;

/**
 * Extends the base AuthStrategyContract to allow handing off SDK clients.
 *
 * An implementation provides direct access to a fully-authenticated external SDK
 * (GoogleAdsClient, FacebookClient, etc) so that adapters are not concerned with
 * client creation or credentials.
 *
 * @template T of object
 */
interface SdkAuthStrategyContract extends AuthStrategyContract
{
    /**
     * Return an authenticated SDK client instance.
     *
     * Implementations should specify a concrete return type in PHPDoc
     * (e.g. GoogleAdsClient, FacebookClient) but remain `object` here
     * to keep this contract provider-agnostic.
     */
    public function getClient(): object;
}
