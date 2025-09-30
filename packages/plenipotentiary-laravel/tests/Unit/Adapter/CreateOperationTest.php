<?php

use Google\Ads\GoogleAds\V21\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateOperation;
use Mockery;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget\RequestMapper as BudgetRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    GoogleAdsDefaults::set('google.customerId', '1234567890');

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->budgetMapper = Mockery::mock(BudgetRequestMapper::class);
    $this->errorMapper = Mockery::mock(ErrorMapperContract::class);
    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->operation = new CreateOperation(
        $this->client,
        $this->budgetMapper,
        $this->errorMapper,
        $this->logger,
    );
});

afterEach(function () {
    Mockery::close();
    GoogleAdsDefaults::set('google.customerId', null);
});

describe('CreateOperation::requestMapper', function () {
    it('builds mutate campaigns request when budget already exists', function () {
        $this->budgetMapper->shouldNotReceive('toBudgetOperation');

        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/42',
        ]);

        [$request, $usesUnified] = $this->operation->requestMapper($dto, false);

        expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
            ->and($usesUnified)->toBeFalse()
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getOperations())->toHaveCount(1)
            ->and($request->getValidateOnly())->toBeFalse();
    });

    it('builds unified mutate request when creating budget on the fly', function () {
        $budgetOperation = new CampaignBudgetOperation([
            'create' => new CampaignBudget([
                'name' => 'Test Campaign Budget',
                'amount_micros' => 1_000_000,
                'resource_name' => 'customers/1234567890/campaignBudgets/-1',
            ]),
        ]);

        $this->budgetMapper
            ->shouldReceive('toBudgetOperation')
            ->once()
            ->andReturn($budgetOperation);

        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'PAUSED',
            'budgetMicros' => 1_000_000,
        ]);

        [$request, $usesUnified] = $this->operation->requestMapper($dto, true);

        expect($request)->toBeInstanceOf(MutateGoogleAdsRequest::class)
            ->and($usesUnified)->toBeTrue()
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeTrue()
            ->and($request->getMutateOperations())->toHaveCount(2)
            ->and($request->getMutateOperations()[0])->toBeInstanceOf(MutateOperation::class)
            ->and($request->getMutateOperations()[0]->getCampaignBudgetOperation())->toBe($budgetOperation);
    });

    it('throws when customer id is missing', function () {
        GoogleAdsDefaults::set('google.customerId', null);

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'No Customer Campaign',
            'status' => 'ENABLED',
            'budgetMicros' => 1_000_000,
        ]);

        expect(fn () => $this->operation->requestMapper($dto, false))
            ->toThrow(InvalidArgumentException::class, 'Missing required provider context key [google.customerId]');
    });
});

describe('CreateOperation::spec', function () {
    it('accepts valid payload', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Valid Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/campaignBudgets/456',
        ]);

        expect(fn () => $this->operation->spec($dto))->not->toThrow(ValidationException::class);
    });

    it('requires name and length <= 128', function () {
        $dtoMissing = CampaignCanonicalDTO::fromArray([
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        $dtoTooLong = CampaignCanonicalDTO::fromArray([
            'name' => str_repeat('A', 129),
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        expect(fn () => $this->operation->spec($dtoMissing))
            ->toThrow(ValidationException::class);
        expect(fn () => $this->operation->spec($dtoTooLong))
            ->toThrow(ValidationException::class);
    });

    it('requires supported status', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test',
            'status' => 'INVALID_STATUS',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        expect(fn () => $this->operation->spec($dto))
            ->toThrow(ValidationException::class);
    });

    it('requires budget resource or micros', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test',
            'status' => 'ENABLED',
        ]);

        expect(fn () => $this->operation->spec($dto))
            ->toThrow(ValidationException::class);
    });

    it('allows budgetMicros in lieu of budget resource', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test',
            'status' => 'PAUSED',
            'budgetMicros' => 2_000_000,
        ]);

        expect(fn () => $this->operation->spec($dto))->not->toThrow(ValidationException::class);
    });
});
