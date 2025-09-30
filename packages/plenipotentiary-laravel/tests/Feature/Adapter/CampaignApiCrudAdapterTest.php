<?php

use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget\RequestMapper as BudgetRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Spec as CreateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\DeleteRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\DeleteResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\Spec as DeleteSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\UpdateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\UpdateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\Spec as UpdateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\Result;

/** @var \Plenipotentiary\Laravel\Tests\Support\TestCase $this */
describe('Campaign API CRUD Adapter', function () {
    beforeEach(function () {
        $this->client = Mockery::mock(ProviderClientContract::class);
        $this->createSpec = new CreateSpec;
        $this->updateSpec = new UpdateSpec;
        $this->deleteSpec = new DeleteSpec;
        $this->createRequestMapper = Mockery::mock(CreateRequestMapperContract::class);
        $this->createResponseMapper = Mockery::mock(CreateResponseMapperContract::class);
        $this->updateRequestMapper = Mockery::mock(UpdateRequestMapperContract::class);
        $this->updateResponseMapper = Mockery::mock(UpdateResponseMapperContract::class);
        $this->deleteRequestMapper = Mockery::mock(DeleteRequestMapperContract::class);
        $this->deleteResponseMapper = Mockery::mock(DeleteResponseMapperContract::class);
        $this->errorMapper = Mockery::mock(ErrorMapperContract::class);
        $this->errorMapper->allows('map')->andReturnUsing(fn ($e) => $e);
        $this->budgetRequestMapper = new BudgetRequestMapper;
        $this->logger = Mockery::mock(\Psr\Log\LoggerInterface::class);

        $this->adapter = new CampaignApiCrudAdapter(
            $this->client,
            $this->createSpec,
            $this->createRequestMapper,
            $this->createResponseMapper,
            $this->budgetRequestMapper,
            $this->updateSpec,
            $this->updateRequestMapper,
            $this->updateResponseMapper,
            $this->deleteSpec,
            $this->deleteRequestMapper,
            $this->deleteResponseMapper,
            $this->errorMapper,
            $this->logger
        );
    });

    it('creates campaign successfully', function () {
        $dto = $this->createTestCampaignDTO();
        $request = Mockery::mock(MutateCampaignsRequest::class);
        $response = Mockery::mock(MutateCampaignsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $campaignServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\Client\CampaignServiceClient::class);
        $expectedResult = $this->createTestCampaignDTO(['externalId' => '123']);

        $this->createRequestMapper->shouldReceive('toCampaignsRequest')
            ->with($dto, false)
            ->andReturn($request);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getCampaignServiceClient')->andReturn($campaignServiceClient);
        $campaignServiceClient->shouldReceive('mutateCampaigns')->with($request)->andReturn($response);
        $this->logger->shouldReceive('info')->once();
        $this->createResponseMapper->shouldReceive('toCanonical')->with($response)->andReturn($expectedResult);

        $result = $this->adapter->create($dto, false);

        expect($result)->toBeInstanceOf(Result::class)
            ->and($result->error())->toBeNull()
            ->and($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBe($expectedResult);
    });

    it('validates campaign before creation', function () {
        $dto = $this->createTestCampaignDTO(['name' => null]);

        $result = $this->adapter->create($dto, false);

        expect($result->isInvalid())->toBeTrue();
    });

    it('handles provider errors', function () {
        $dto = $this->createTestCampaignDTO();
        $exception = new \RuntimeException('Provider error');
        $mappedException = new \DomainException('Mapped error');

        $this->createRequestMapper->shouldReceive('toCampaignsRequest')
            ->with($dto, false)
            ->andThrow($exception);
        $this->errorMapper->shouldReceive('map')->with($exception)->andReturn($mappedException);

        $result = $this->adapter->create($dto, false);

        expect($result->isErr())->toBeTrue()
            ->and($result->error())->toHaveKey('class', \RuntimeException::class);
    });

    it('creates campaign with budget on the fly', function () {
        $dto = $this->createTestCampaignDTO([
            'budgetResourceName' => null,
            'budgetMicros' => 2000000,
        ]);
        $unifiedRequest = Mockery::mock(MutateGoogleAdsRequest::class);
        $response = Mockery::mock(MutateGoogleAdsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $googleAdsServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\Client\GoogleAdsServiceClient::class);
        $expectedResult = $this->createTestCampaignDTO(['externalId' => '123']);

        $capturedBudgetOp = null;
        $this->createRequestMapper->shouldReceive('toUnifiedRequest')
            ->with($dto, false, Mockery::on(function ($op) use (&$capturedBudgetOp) {
                $capturedBudgetOp = $op;
                return $op instanceof CampaignBudgetOperation;
            }))
            ->andReturn($unifiedRequest);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getGoogleAdsServiceClient')->andReturn($googleAdsServiceClient);
        $googleAdsServiceClient->shouldReceive('mutate')->with($unifiedRequest)->andReturn($response);
        $this->logger->shouldReceive('info')->once();
        $this->createResponseMapper->shouldReceive('toCanonical')->with($response)->andReturn($expectedResult);

        $result = $this->adapter->create($dto, false);
        $error = $result->error();

        expect($error['class'] ?? null)->toBeNull()
            ->and($error['message'] ?? null)->toBeNull()
            ->and($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBe($expectedResult);
        expect($capturedBudgetOp)->toBeInstanceOf(CampaignBudgetOperation::class);
    });

    it('performs validate-only creation', function () {
        $dto = $this->createTestCampaignDTO();
        $request = Mockery::mock(MutateCampaignsRequest::class);
        $response = Mockery::mock(MutateCampaignsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $campaignServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\Client\CampaignServiceClient::class);

        $this->createRequestMapper->shouldReceive('toCampaignsRequest')
            ->with($dto, true)
            ->andReturn($request);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getCampaignServiceClient')->andReturn($campaignServiceClient);
        $campaignServiceClient->shouldReceive('mutateCampaigns')->with($request)->andReturn($response);
        $this->logger->shouldReceive('info')->once();

        $result = $this->adapter->create($dto, true);

        expect($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBeNull();
    });

    it('updates campaign successfully', function () {
        $dto = $this->createTestCampaignDTO([
            'identifiers' => ['resourceName' => 'customers/1234567890/campaigns/456'],
            'status' => 'PAUSED',
        ]);
        $request = Mockery::mock(MutateCampaignsRequest::class);
        $response = Mockery::mock(MutateCampaignsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $campaignServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\Client\CampaignServiceClient::class);
        $expected = new CampaignCanonicalDTO;

        $this->updateRequestMapper->shouldReceive('toRequest')
            ->with($dto, false)
            ->andReturn($request);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getCampaignServiceClient')->andReturn($campaignServiceClient);
        $campaignServiceClient->shouldReceive('mutateCampaigns')->with($request)->andReturn($response);
        $this->logger->shouldReceive('info')->once();
        $this->updateResponseMapper->shouldReceive('toCanonical')
            ->with($response)
            ->andReturn($expected);

        $result = $this->adapter->update($dto, false);

        expect($result->error())->toBeNull()
            ->and($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBe($expected);
    });

    it('performs validate-only update', function () {
        $dto = $this->createTestCampaignDTO([
            'identifiers' => ['resourceName' => 'customers/1234567890/campaigns/456'],
        ]);
        $request = Mockery::mock(MutateCampaignsRequest::class);
        $response = Mockery::mock(MutateCampaignsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $campaignServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\Client\CampaignServiceClient::class);

        $this->updateRequestMapper->shouldReceive('toRequest')
            ->with($dto, true)
            ->andReturn($request);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getCampaignServiceClient')->andReturn($campaignServiceClient);
        $campaignServiceClient->shouldReceive('mutateCampaigns')->with($request)->andReturn($response);
        $this->logger->shouldReceive('info')->once();

        $result = $this->adapter->update($dto, true);

        expect($result->error())->toBeNull()
            ->and($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBeNull();
    });
});
