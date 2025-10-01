<?php

use Plenipotentiary\Laravel\Support\CanonicalFactory;
use Plenipotentiary\Laravel\Support\InputSource\ArraySource;

it('builds DTO instances from input sources', function () {
    $factory = new CanonicalFactory();

    /** @var FakeCanonicalDto $dto */
    $dto = $factory->make(FakeCanonicalDto::class, [
        new ArraySource([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
        ]),
    ]);

    expect($dto)->toBeInstanceOf(FakeCanonicalDto::class)
        ->and($dto->name)->toBe('Test Campaign')
        ->and($dto->status)->toBe('ENABLED');
});

it('throws when DTO contract is invalid', function () {
    $factory = new CanonicalFactory();

    expect(fn () => $factory->make(MissingFromArrayDto::class, []))
        ->toThrow(\InvalidArgumentException::class);
});

it('throws when fromArray does not return an object', function () {
    $factory = new CanonicalFactory();

    expect(fn () => $factory->make(NonObjectReturningDto::class, []))
        ->toThrow(\RuntimeException::class);
});

final class FakeCanonicalDto
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $status,
    ) {}

    public static function schema(): array
    {
        return [
            'name' => ['key' => 'name'],
            'status' => ['key' => 'status'],
        ];
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['name'] ?? null,
            $payload['status'] ?? null,
        );
    }
}

final class MissingFromArrayDto
{
    public static function schema(): array
    {
        return [];
    }
}

final class NonObjectReturningDto
{
    public static function schema(): array
    {
        return [];
    }

    public static function fromArray(array $payload): array
    {
        return $payload;
    }
}
