<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;

beforeEach(function () {
    GoogleAdsDefaults::set('google.customerId', '1234567890');
});

describe('CampaignCanonicalDTO', function () {
    it('creates from array with defaults applied lazily', function () {
        $dto = CampaignCanonicalDTO::fromArray([]);

        expect($dto->providerContext)->toBeEmpty()
            ->and($dto->internalId)->toBeNull()
            ->and($dto->externalId)->toBeNull()
            ->and($dto->identifiers)->toBe([])
            ->and($dto->name)->toBeNull()
            ->and($dto->status)->toBeNull()
            ->and($dto->budgetResourceName)->toBeNull()
            ->and($dto->cpcBidMicros)->toBeNull()
            ->and($dto->budgetMicros)->toBeNull()
            ->and($dto->providerContextValue('google.customerId'))->toBe('1234567890');
    });

    it('creates from array with data', function () {
        $data = [
            'providerContext' => ['google.customerId' => '1234567890'],
            'internalId' => 'internal-123',
            'externalId' => 'external-456',
            'identifiers' => ['resourceName' => 'customers/123/campaigns/456'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/budgets/789',
            'cpcBidMicros' => '1000000',
            'budgetMicros' => '5000000',
        ];

        $dto = CampaignCanonicalDTO::fromArray($data);

        expect($dto->providerContext)->toBe($data['providerContext'])
            ->and($dto->internalId)->toBe('internal-123')
            ->and($dto->externalId)->toBe('external-456')
            ->and($dto->identifiers)->toBe($data['identifiers'])
            ->and($dto->name)->toBe('Test Campaign')
            ->and($dto->status)->toBe('ENABLED')
            ->and($dto->budgetResourceName)->toBe('customers/123/budgets/789')
            ->and($dto->cpcBidMicros)->toBe(1000000)
            ->and($dto->budgetMicros)->toBe(5000000);
    });

    it('converts to array', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
        ]);

        $array = $dto->toArray();

        expect($array)->toHaveKey('name', 'Test Campaign')
            ->and($array)->toHaveKey('status', 'ENABLED')
            ->and($array)->toHaveKey('providerContext')
            ->and($array)->toHaveKey('identifiers');
    });

    it('provides accessor methods', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'externalId' => 'external-123',
            'internalId' => 'internal-456',
            'identifiers' => ['resourceName' => 'customers/123/campaigns/456'],
        ]);

        expect($dto->externalId())->toBe('external-123')
            ->and($dto->internalId())->toBe('internal-456')
            ->and($dto->identifier('resourceName'))->toBe('customers/123/campaigns/456')
            ->and($dto->identifier('nonexistent'))->toBeNull();
    });
});
