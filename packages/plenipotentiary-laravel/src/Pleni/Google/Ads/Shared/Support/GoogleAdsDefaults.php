<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support;

/**
 * Central place for Google Ads default values used across selectors and DTOs.
 *
 * Values can be configured explicitly (set()) or loaded from environment/config.
 */
final class GoogleAdsDefaults
{
    /** @var array<string,string|null> */
    private static array $defaults = [
        'google.customerId' => null,
    ];

    private static bool $booted = false;

    /**
     * Load default values from environment once.
     */
    public static function loadFromEnv(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;
        $linkedCustomer = env('GOOGLE_ADS_LINKED_CUSTOMER_ID');
        if ($linkedCustomer !== null && $linkedCustomer !== '') {
            self::$defaults['google.customerId'] = $linkedCustomer;
        }
    }

    /**
     * Explicitly set a default value at runtime.
     */
    public static function set(string $key, ?string $value): void
    {
        self::$defaults[$key] = $value;
    }

    public static function get(string $key): ?string
    {
        self::loadFromEnv();
        return self::$defaults[$key] ?? null;
    }

    /**
     * Merge provided context with configured defaults (defaults apply when missing).
     *
     * @param  array<string,string>  $context
     * @return array<string,string>
     */
    public static function hydrate(array $context): array
    {
        self::loadFromEnv();

        $merged = self::$defaults;
        foreach ($context as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $merged[$key] = $value;
        }

        return array_filter($merged, fn ($value) => $value !== null && $value !== '');
    }
}
