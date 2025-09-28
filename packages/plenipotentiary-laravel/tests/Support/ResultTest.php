<?php

use Plenipotentiary\Laravel\Pleni\Support\Result;

describe('Result', function () {
    it('creates ok result with value', function () {
        $result = Result::ok('test value');
        
        expect($result->isOk())->toBeTrue()
            ->and($result->isErr())->toBeFalse()
            ->and($result->isInvalid())->toBeFalse()
            ->and($result->unwrap())->toBe('test value');
    });

    it('creates ok result without value', function () {
        $result = Result::ok();
        
        expect($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBeNull();
    });

    it('creates error result with throwable', function () {
        $exception = new \RuntimeException('Test error');
        $result = Result::err($exception);
        
        expect($result->isErr())->toBeTrue()
            ->and($result->error())->toHaveKey('class', \RuntimeException::class)
            ->and($result->error())->toHaveKey('message', 'Test error');
    });

    it('creates error result with string', function () {
        $result = Result::err('Simple error');
        
        expect($result->isErr())->toBeTrue()
            ->and($result->error())->toHaveKey('error', 'Simple error');
    });

    it('creates invalid result with violations', function () {
        $violations = [
            ['field' => 'name', 'rule' => 'required'],
            ['field' => 'email', 'rule' => 'email'],
        ];
        $result = Result::invalid($violations);
        
        expect($result->isInvalid())->toBeTrue()
            ->and($result->violations())->toBe($violations);
    });

    it('maps ok values', function () {
        $result = Result::ok(5);
        $mapped = $result->map(fn($x) => $x * 2);
        
        expect($mapped->isOk())->toBeTrue()
            ->and($mapped->unwrap())->toBe(10);
    });

    it('does not map error values', function () {
        $result = Result::err('error');
        $mapped = $result->map(fn($x) => $x * 2);
        
        expect($mapped)->toBe($result);
    });

    it('maps error payloads', function () {
        $result = Result::err('original error');
        $mapped = $result->mapError(fn($error) => 'mapped: ' . $error['error']);
        
        expect($mapped->isErr())->toBeTrue()
            ->and($mapped->error())->toHaveKey('error', 'mapped: original error');
    });

    it('serializes to array', function () {
        $result = Result::ok('test');
        $array = $result->toArray();
        
        expect($array)->toHaveKey('kind', 'ok')
            ->and($array)->toHaveKey('payload', 'test');
    });

    it('throws when unwrapping non-ok result', function () {
        $result = Result::err('error');
        
        expect(fn() => $result->unwrap())
            ->toThrow(\LogicException::class, 'Attempted to unwrap a non-ok Result');
    });
});