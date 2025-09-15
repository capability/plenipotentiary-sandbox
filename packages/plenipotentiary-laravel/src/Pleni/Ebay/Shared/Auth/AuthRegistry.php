<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Ebay\Shared\Auth;

use Plenipotentiary\Laravel\Contracts\AuthStrategy;

/**
 * Registry/factory to resolve AuthStrategy per domain/scope.
 * Keeps different auth strategies separated while sharing config.
 */
final class AuthRegistry
{
    /** @var array<string, AuthStrategy> */
    private array $strategies = [];

    public function __construct(private readonly array $config)
    {
        $cfg = config('pleni_auth');
    }

    public function register(string $key, AuthStrategy $strategy): void
    {
        $this->strategies[$key] = $strategy;
    }

    public function for(string $provider, string $domain): AuthStrategy
    {
        $key = strtolower($provider.'.'.$domain);
        $cfg = config('pleni_auth');
        $driverKey = $cfg['defaults'][$provider][$domain] ?? null;

        if (! isset($this->strategies[$key]) && ! $driverKey) {
            throw new \RuntimeException("No auth driver configured for {$provider}.{$domain}");
        }

        $driverCfg = $cfg['drivers'][$driverKey] ?? [];

        return match ($driverKey) {
            null => $this->strategies[$key],
            default => $this->strategies[$driverKey] ??= [
                'ebay_client_credentials' => new \Plenipotentiary\Laravel\Pleni\Ebay\Shared\Auth\EbayClientCredentials(
                    clientId: (string) ($driverCfg['client_id'] ?? ''),
                    clientSecret: (string) ($driverCfg['client_secret'] ?? ''),
                    tokenUrl: (string) ($driverCfg['token_url'] ?? ''),
                    scopes: (array) ($driverCfg['scopes'] ?? []),
                ),
            ][$driverKey],
        };
    }
}
