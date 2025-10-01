<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCreate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignUpdate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignDelete;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignRead;
use Plenipotentiary\Laravel\Support\Commands\GenerateCanonicalFromErrorCommand;

it('generates canonical payload from expected structure', function () {
    $error = [
        'expected' => [
            'dto' => [
                'fields' => [
                    'name' => ['required' => true, 'type' => 'string'],
                    'status' => ['required' => true, 'type' => 'enum', 'values' => ['ENABLED']],
                    'budgetMicros' => ['required' => true, 'type' => 'numeric', 'cast' => 'currency_to_micros'],
                ],
                'providerContext' => [
                    'google.customerId' => ['required' => true, 'type' => 'string', 'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID'],
                ],
            ],
        ],
    ];

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID=9999999999');

    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => json_encode($error, JSON_THROW_ON_ERROR),
    ])->expectsOutputToContain('Canonical DTO array:')
        ->expectsOutputToContain('google.customerId')
        ->assertSuccessful();

    $dto = CampaignCanonicalDTO::fromArray([
        'name' => '<string>',
        'status' => 'ENABLED',
        'budgetMicros' => 1_000_000,
        'providerContext' => [
            'google.customerId' => '9999999999',
        ],
    ]);

    expect($dto->getProviderContextValue('google.customerId'))->toBe('9999999999');

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID');
});

it('falls back to operation input spec when expected structure omits required values', function () {
    $error = [
        'expected' => [
            'dto' => [
                'fields' => [],
                'providerContext' => [],
            ],
        ],
    ];

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID=8888888888');

    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => json_encode($error, JSON_THROW_ON_ERROR),
        '--operation' => CampaignCreate::class,
        '--pretty' => true,
    ])->expectsOutputToContain('Missing required field "name" in error payload; inferred from CampaignCreate::INPUT_SPEC.')
        ->expectsOutputToContain('google.customerId')
        ->assertSuccessful();

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID');
});

it('infers update operation provider context placeholders from INPUT_SPEC', function () {
    $error = [
        'expected' => [
            'dto' => [
                'fields' => [],
                'providerContext' => [],
            ],
        ],
    ];

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID=7777777777');

    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => json_encode($error, JSON_THROW_ON_ERROR),
        '--operation' => CampaignUpdate::class,
        '--pretty' => true,
    ])->expectsOutputToContain('Missing required provider context "google.customerId" in error payload; inferred from CampaignUpdate::INPUT_SPEC.')
        ->expectsOutputToContain('Missing required provider context "resourceName" in error payload; inferred from CampaignUpdate::INPUT_SPEC.')
        ->expectsOutputToContain('resourceName')
        ->assertSuccessful();

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID');
});

it('infers delete operation placeholders from INPUT_SPEC', function () {
    $error = [
        'expected' => [
            'dto' => [
                'fields' => [],
                'providerContext' => [],
            ],
        ],
    ];

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID=6666666666');

    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => json_encode($error, JSON_THROW_ON_ERROR),
        '--operation' => CampaignDelete::class,
        '--pretty' => true,
    ])->expectsOutputToContain('Missing required provider context "google.customerId" in error payload; inferred from CampaignDelete::INPUT_SPEC.')
        ->expectsOutputToContain('externalId')
        ->assertSuccessful();

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID');
});

it('infers read operation placeholders from INPUT_SPEC', function () {
    $error = [
        'expected' => [
            'dto' => [
                'fields' => [],
                'providerContext' => [],
            ],
        ],
    ];

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID=5555555555');

    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => json_encode($error, JSON_THROW_ON_ERROR),
        '--operation' => CampaignRead::class,
        '--pretty' => true,
    ])->expectsOutputToContain('Missing required provider context "google.customerId" in error payload; inferred from CampaignRead::INPUT_SPEC.')
        ->expectsOutputToContain('externalId')
        ->assertSuccessful();

    putenv('GOOGLE_ADS_LINKED_CUSTOMER_ID');
});

it('fails when error payload lacks expected structure', function () {
    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => json_encode(['message' => 'no expected structure'], JSON_THROW_ON_ERROR),
    ])->expectsOutputToContain('Error payload does not contain an "expected" structure.')
        ->assertFailed();
});

it('fails when JSON cannot be decoded', function () {
    $this->artisan(GenerateCanonicalFromErrorCommand::class, [
        '--error' => '{invalid-json',
    ])->expectsOutputToContain('Invalid JSON payload')
        ->assertFailed();
});
