<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support\Logging;

final class Redactor
{
    /** @var string[] Default sensitive header names (lowercased) */
    private static array $SENSITIVE_HEADERS = [
        'authorization', 'cookie', 'set-cookie', 'x-api-key', 'x-auth-token',
    ];

    /** @var string[] Default sensitive body keys (lowercased) */
    private static array $SENSITIVE_KEYS = [
        'access_token', 'refresh_token', 'password', 'secret', 'client_secret',
        'authorization', 'token', 'id_token',
    ];

    /**
     * Redact sensitive headers. Header names are compared case-insensitively.
     * @param array<string,mixed> $headers
     * @return array<string,mixed>
     */
    public static function headers(array $headers): array
    {
        $out = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower((string)$k);
            if (in_array($lk, self::$SENSITIVE_HEADERS, true)) {
                $out[$k] = '***REDACTED***';
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * Redact sensitive body keys; optionally hash specific fields.
     * @param array<string,mixed> $body
     * @param string[] $fieldsToHash
     * @return array<string,mixed>
     */
    public static function body(array $body, array $fieldsToHash = []): array
    {
        $out = [];
        foreach ($body as $k => $v) {
            $lk = strtolower((string)$k);

            if (in_array($lk, self::$SENSITIVE_KEYS, true)) {
                $out[$k] = '***REDACTED***';
                continue;
            }

            if (in_array($k, $fieldsToHash, true) && is_scalar($v)) {
                $out[$k] = self::hash((string)$v);
                continue;
            }

            $out[$k] = is_array($v) ? '[array]' : (is_object($v) ? '[object]' : $v);
        }
        return $out;
    }

    /**
     * Hash a value for safe logging/correlation.
     */
    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }
}