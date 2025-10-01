<?php

declare(strict_types=1);

use Plenipotentiary\Laravel\Support\Operation\OperationDescription;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;

describe('Operation support objects', function () {
    it('creates serialisable operation descriptions', function () {
        $rules = [
            ['field' => 'name', 'rule' => 'required'],
            ['field' => 'status', 'rule' => 'in:ENABLED,PAUSED'],
        ];

        $description = OperationDescription::make('campaign.create', $rules);

        expect($description->toArray())
            ->toMatchArray([
                'operation' => 'campaign.create',
                'rules' => $rules,
            ])
            ->and(json_decode((string) json_encode($description), true))
                ->toMatchArray($description->toArray());
    });

    it('creates validation exceptions with context', function () {
        $violations = [
            ['field' => 'name', 'rule' => 'required'],
        ];

        $exception = ValidationException::fromArray('campaign.create', $violations);

        expect($exception)
            ->toBeInstanceOf(ValidationException::class)
            ->and($exception->getMessage())->toBe('Validation failed for campaign.create')
            ->and($exception->violations())->toBe($violations)
            ->and($exception->toArray())
                ->toMatchArray([
                    'operation' => 'campaign.create',
                    'violations' => $violations,
                ])
            ->and(json_decode((string) json_encode($exception), true))
                ->toMatchArray($exception->toArray());
    });
});
