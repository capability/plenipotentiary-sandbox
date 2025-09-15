<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

/**
 * Base exception mapper contract for plenipotentiary integrations.
 *
 * All integration-specific mappers (e.g. GoogleAdsExceptionMapper, EbayExceptionMapper)
 * should extend or mirror this behaviour so User Services always handle errors consistently.
 */
abstract class ExceptionMapper
{
    /**
     * Map any \Throwable into a domain-level \Throwable.
     * Implementations are responsible for interpreting vendor/SDK details.
     */
    abstract public static function map(\Throwable $e, string $contextMessage): \Throwable;
}
