<?php

declare(strict_types=1);

use Plenipotentiary\Laravel\Support\Validation\InputSpecValidator;

describe('InputSpecValidator', function () {
    $spec = [
        'name' => [
            'rules' => ['required', 'string', 'min:3', 'max:10'],
            'mapsTo' => 'campaign.name',
        ],
        'budget' => [
            'rules' => ['numeric', 'min:100'],
            'cast' => 'int',
            'default' => 150,
        ],
        'status' => [
            'rules' => ['in:ENABLED,PAUSED'],
            'default' => 'ENABLED',
        ],
        'providerContext.accountId' => [
            'rules' => ['required', 'string'],
            'mapsTo' => 'provider.account',
            'source' => 'providerContext',
        ],
        'description' => [
            'rules' => ['nullable', 'string', 'max:20'],
        ],
    ];

    it('returns violations and expected structure when values fail validation', function () use ($spec) {
        $values = [
            'budget' => 50,
            'status' => 'ARCHIVED',
            'description' => null,
        ];

        $result = InputSpecValidator::validate($spec, $values);

        expect($result)
            ->toHaveKey('expected')
            ->and($result['expected']['dto']['fields']['name'])
            ->toMatchArray([
                'required' => true,
                'rules' => ['required', 'string', 'min:3', 'max:10'],
                'type' => 'string',
            ])
            ->and($result['expected']['dto']['providerContext']['accountId'])
            ->toMatchArray([
                'required' => true,
                'rules' => ['required', 'string'],
                'source' => 'providerContext',
                'type' => 'string',
            ]);

        expect($result['violations'])
            ->toHaveCount(4)
            ->sequence(
                fn ($expectation) => $expectation->toMatchArray([
                    'field' => 'name',
                    'rule' => 'required',
                    'mapsTo' => 'campaign.name',
                    'message' => 'Required',
                ]),
                fn ($expectation) => $expectation->toMatchArray([
                    'field' => 'budget',
                    'rule' => 'min:100',
                    'message' => 'Must be at least 100',
                ]),
                fn ($expectation) => $expectation->toMatchArray([
                    'field' => 'status',
                    'rule' => 'in:ENABLED,PAUSED',
                    'message' => 'Must be one of: ENABLED,PAUSED',
                ]),
                fn ($expectation) => $expectation->toMatchArray([
                    'field' => 'providerContext.accountId',
                    'rule' => 'required',
                    'mapsTo' => 'provider.account',
                    'message' => 'Required',
                ])
            );
    });

    it('passes validation when values satisfy rules', function () use ($spec) {
        $values = [
            'name' => 'Acme',
            'budget' => 250,
            'status' => 'ENABLED',
            'providerContext.accountId' => '123456',
            'description' => 'Optional',
        ];

        $result = InputSpecValidator::validate($spec, $values);

        expect($result['violations'])->toBeEmpty()
            ->and($result['expected']['dto']['fields']['budget'])
            ->toMatchArray([
                'required' => false,
                'rules' => ['numeric', 'min:100'],
                'cast' => 'int',
                'type' => 'numeric',
                'default' => 150,
            ]);
    });
});
