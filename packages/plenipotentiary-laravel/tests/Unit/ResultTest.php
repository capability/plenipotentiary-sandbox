<?php

declare(strict_types=1);

use Plenipotentiary\Laravel\Support\Result;

describe('Result', function () {
    it('wraps a canonical DTO in an ok result', function () {
        $dto = $this->createTestCampaignDTO(['name' => 'Example Campaign']);

        $result = Result::ok($dto);

        $array = $result->toArray();

        expect($result->isOk())->toBeTrue()
            ->and($result->isErr())->toBeFalse()
            ->and($result->isInvalid())->toBeFalse()
            ->and($result->dto())->toBe($dto)
            ->and($result->unwrap())->toBe($dto)
            ->and($array)->toMatchArray([
                'kind' => 'ok',
                'dto' => $dto->toArray(),
            ])
            ->and($array['payload'])->toBe($dto);
    });

    it('normalises throwable errors', function () {
        $dto = $this->createTestCampaignDTO();
        $exception = new RuntimeException('Explosion');

        $result = Result::err($exception, $dto);

        expect($result->isErr())->toBeTrue()
            ->and($result->dto())->toBe($dto)
            ->and($result->error())
                ->toMatchArray([
                    'error' => 'Exception',
                    'class' => RuntimeException::class,
                    'message' => 'Explosion',
                ]);
    });

    it('normalises string errors', function () {
        $result = Result::err('Something went wrong');

        expect($result->isErr())->toBeTrue()
            ->and($result->error())
                ->toMatchArray([
                    'error' => 'Something went wrong',
                ]);
    });

    it('records invalid violations and expected structure', function () {
        $dto = $this->createTestCampaignDTO();
        $violations = [
            ['field' => 'name', 'rule' => 'required'],
        ];
        $expected = [
            'dto' => [
                'fields' => ['name' => ['required' => true]],
                'providerContext' => [],
            ],
        ];

        $result = Result::invalid($violations, $expected, $dto);

        expect($result->isInvalid())->toBeTrue()
            ->and($result->dto())->toBe($dto)
            ->and($result->violations())->toBe($violations)
            ->and($result->toArray())
                ->toMatchArray([
                    'kind' => 'invalid',
                    'payload' => [
                        'expected' => $expected,
                        'violations' => $violations,
                    ],
                ]);
    });

    it('maps ok results with canonical dto', function () {
        $result = Result::ok($this->createTestCampaignDTO(['name' => 'Before']));

        $mapped = $result->map(function ($dto) {
            $data = $dto->toArray();
            $data['name'] = 'After';

            return $dto::fromArray($data);
        });

        expect($mapped)->not()->toBe($result)
            ->and($mapped->isOk())->toBeTrue()
            ->and($mapped->unwrap()->name)->toBe('After');
    });

    it('keeps non-ok results when mapping', function () {
        $result = Result::err('Boom');

        expect($result->map(fn () => throw new LogicException('should not run')))
            ->toBe($result);
    });

    it('maps error payloads', function () {
        $result = Result::err('Original Problem');

        $mapped = $result->mapError(function (array $payload) {
            return [
                'error' => sprintf('Handled: %s', $payload['error']),
            ];
        });

        expect($mapped)->not()->toBe($result)
            ->and($mapped->isErr())->toBeTrue()
            ->and($mapped->error())
                ->toMatchArray(['error' => 'Handled: Original Problem']);
    });

    it('maps invalid violations', function () {
        $result = Result::invalid([
            ['field' => 'name', 'rule' => 'required'],
        ]);

        $mapped = $result->mapError(function (array $violations) {
            return array_map(
                fn ($violation) => $violation + ['message' => 'Custom message'],
                $violations
            );
        });

        expect($mapped->isInvalid())->toBeTrue()
            ->and($mapped->violations())->toMatchArray([
                ['field' => 'name', 'rule' => 'required', 'message' => 'Custom message'],
            ]);
    });

    it('throws when unwrapping non-ok result', function () {
        $result = Result::err('nope');

        expect(fn () => $result->unwrap())
            ->toThrow(LogicException::class, 'Attempted to unwrap a non-ok Result');
    });

    it('serialises to json consistently', function () {
        $dto = $this->createTestCampaignDTO(['name' => 'Serializable']);
        $result = Result::ok($dto);

        $json = json_decode(json_encode($result, JSON_THROW_ON_ERROR), true);

        expect($json)
            ->toMatchArray([
                'kind' => 'ok',
                'dto' => $dto->toArray(),
            ])
            ->and($json['payload'])
                ->toMatchArray($dto->toArray());
    });
});
