<?php

use Plenipotentiary\Laravel\Support\Result;

describe('Result helper assertions', function () {
    it('creates an ok result from a canonical DTO', function () {
        $dto = $this->createTestCampaignDTO(['name' => 'Support Suite']);
        $result = Result::ok($dto);

        expect($result->isOk())->toBeTrue()
            ->and($result->dto())->toBe($dto)
            ->and($result->unwrap())->toBe($dto);
    });

    it('creates an error result from a throwable', function () {
        $exception = new \RuntimeException('Support test error');
        $result = Result::err($exception);

        expect($result->isErr())->toBeTrue()
            ->and($result->error())
                ->toMatchArray([
                    'error' => 'Exception',
                    'class' => \RuntimeException::class,
                    'message' => 'Support test error',
                ]);
    });

    it('creates an invalid result with violations', function () {
        $violations = [
            ['field' => 'name', 'rule' => 'required'],
        ];

        $result = Result::invalid($violations);

        expect($result->isInvalid())->toBeTrue()
            ->and($result->violations())->toBe($violations);
    });

    it('maps ok values without affecting errors', function () {
        $dto = $this->createTestCampaignDTO(['name' => 'Pre-map']);
        $mapped = Result::ok($dto)->map(function ($dto) {
            $data = $dto->toArray();
            $data['name'] = 'Post-map';

            return $dto::fromArray($data);
        });

        expect($mapped->unwrap()->name)->toBe('Post-map');

        $error = Result::err('error');
        expect($error->map(fn () => throw new \RuntimeException('should not run')))
            ->toBe($error);
    });

    it('throws when unwrapping non-ok result', function () {
        $result = Result::err('error');

        expect(fn () => $result->unwrap())
            ->toThrow(\LogicException::class, 'Attempted to unwrap a non-ok Result');
    });
});
