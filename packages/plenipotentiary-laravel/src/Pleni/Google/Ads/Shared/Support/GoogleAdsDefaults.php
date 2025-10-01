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
        $linkedCustomer = GoogleAdsConfig::linkedCustomerId();
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
     * Merge provided context with defaults without mutating input.
     *
     * @param  array<string,string|null>  $context
     * @return array<string,string>
     */
    public static function apply(array $context): array
    {
        self::loadFromEnv();

        $normalised = [];
        foreach ($context as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $normalised[$key] = (string) $value;
        }

        return array_filter(array_merge(self::$defaults, $normalised), fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Apply defaults and ensure required keys are present.
     *
     * @param  array<string,string|null>  $context
     * @return array<string,string>
     */
    public static function require(array $context, string ...$requiredKeys): array
    {
        $merged = self::apply($context);

        foreach ($requiredKeys as $key) {
            if (! isset($merged[$key]) || $merged[$key] === '') {
                throw new \InvalidArgumentException(sprintf('Missing required provider context key [%s].', $key));
            }
        }

        return $merged;
    }
}
