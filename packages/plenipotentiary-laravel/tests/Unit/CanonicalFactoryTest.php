<?php

declare(strict_types=1);

use Plenipotentiary\Laravel\Support\CanonicalFactory;
use Plenipotentiary\Laravel\Support\InputSource\ArraySource;
use Plenipotentiary\Laravel\Tests\Unit\Stubs\FakeCanonicalDto;

describe('CanonicalFactory', function () {
    it('hydrates DTOs from input sources and applies casts, defaults, and overrides', function () {
        $factory = new CanonicalFactory;

        $sources = [
            new ArraySource([
                'name' => 'Primary Name',
                'micros' => 1.5,
                'custom' => 'make uppercase',
            ]),
            new ArraySource([
                'name' => 'Secondary Name',
                'custom' => 'fallback',
            ]),
        ];

        $dto = $factory->make(
            FakeCanonicalDto::class,
            $sources,
            [
                'amount' => 99,
                'providerContext' => ['account' => 'abc-123'],
            ]
        );

        expect($dto)
            ->toBeInstanceOf(FakeCanonicalDto::class)
            ->and($dto->name)->toBe('Primary Name')
            ->and($dto->amount)->toBe(99)
            ->and($dto->micros)->toBe(1_500_000)
            ->and($dto->custom)->toBe('MAKE UPPERCASE')
            ->and($dto->providerContext)->toMatchArray(['account' => 'abc-123']);
    });

    it('throws when the DTO class does not expose required factories', function () {
        $factory = new CanonicalFactory;

        expect(fn () => $factory->make(MissingFactoryDto::class, []))
            ->toThrow(\InvalidArgumentException::class, 'MissingFactoryDto must expose schema() and fromArray().');
    });

    it('ensures DTO factories return objects', function () {
        $factory = new CanonicalFactory;

        expect(fn () => $factory->make(NonObjectFactoryDto::class, []))
            ->toThrow(\RuntimeException::class, 'NonObjectFactoryDto::fromArray must return a DTO instance.');
    });
});

if (! class_exists('MissingFactoryDto')) {
    final class MissingFactoryDto
    {
        public static function schema(): array
        {
            return [];
        }
    }
}

if (! class_exists('NonObjectFactoryDto')) {
    final class NonObjectFactoryDto
    {
        public static function schema(): array
        {
            return [];
        }

        public static function fromArray(array $data): string
        {
            return 'not-an-object';
        }
    }
}
