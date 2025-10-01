<?php

declare(strict_types=1);

use Plenipotentiary\Laravel\Support\Logging\Redactor;

describe('Logging Redactor', function () {
    it('redacts sensitive headers case-insensitively', function () {
        $headers = [
            'Authorization' => 'Bearer secret',
            'X-CUSTOM' => 'value',
            'cookie' => 'session-id',
        ];

        $redacted = Redactor::headers($headers);

        expect($redacted)
            ->toMatchArray([
                'Authorization' => '***REDACTED***',
                'X-CUSTOM' => 'value',
                'cookie' => '***REDACTED***',
            ]);
    });

    it('redacts bodies and hashes selected fields', function () {
        $body = [
            'password' => 'p@ss',
            'token' => 'secret',
            'customerId' => '123-xyz',
            'metadata' => ['nested' => true],
            'payload' => (object) ['value' => 1],
            'description' => 'visible',
        ];

        $redacted = Redactor::body($body, ['customerId']);

        expect($redacted['password'])->toBe('***REDACTED***')
            ->and($redacted['token'])->toBe('***REDACTED***')
            ->and($redacted['customerId'])
            ->toBe(Redactor::hash('123-xyz'))
            ->and($redacted['metadata'])->toBe('[array]')
            ->and($redacted['payload'])->toBe('[object]')
            ->and($redacted['description'])->toBe('visible');
    });

    it('hashes values deterministically', function () {
        expect(Redactor::hash('abc123'))
            ->toBe(Redactor::hash('abc123'))
            ->and(Redactor::hash('abc123'))
            ->not()->toBe(Redactor::hash('different'));
    });
});
