<?php

use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Support\Result;

describe('Campaign API CRUD Adapter', function () {
    beforeEach(function () {
        $this->client = Mockery::mock(ProviderClientContract::class);
        $this->createSpec = Mockery::mock(\Plenipotentiary\Laravel\Contracts\Adapter\SpecContract::class);
        $this->createRequestMapper = Mockery::mock(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateRequestMapperContract::class);
        $this->createResponseMapper = Mockery::mock(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateResponseMapperContract::class);
        $this->errorMapper = Mockery::mock(ErrorMapperContract::class);
        $this->budgetRequestMapper = Mockery::mock(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget\RequestMapper::class);
        $this->logger = Mockery::mock(\Psr\Log\LoggerInterface::class);
        
        $this->adapter = new CampaignApiCrudAdapter(
            $this->client,
            $this->createSpec,
            $this->createRequestMapper,
            $this->createResponseMapper,
            $this->errorMapper,
            $this->budgetRequestMapper,
            $this->logger
        );
    });

    it('creates campaign successfully', function () {
        $dto = $this->createTestCampaignDTO();
        $request = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest::class);
        $response = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $campaignServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\CampaignServiceClient::class);
        $expectedResult = $this->createTestCampaignDTO(['externalId' => '123']);
        
        $this->createSpec->shouldReceive('preflight')->with($dto)->once();
        $this->createRequestMapper->shouldReceive('toCampaignsRequest')
            ->with($dto, false)
            ->andReturn($request);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getCampaignServiceClient')->andReturn($campaignServiceClient);
        $campaignServiceClient->shouldReceive('mutateCampaigns')->with($request)->andReturn($response);
        $this->logger->shouldReceive('info')->once();
        $this->createResponseMapper->shouldReceive('toCanonical')->with($response)->andReturn($expectedResult);
        
        $result = $this->adapter->create($dto, false);
        
        expect($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBe($expectedResult);
    });

    it('validates campaign before creation', function () {
        $dto = $this->createTestCampaignDTO();
        
        $this->createSpec->shouldReceive('preflight')
            ->with($dto)
            ->andThrow(new \Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException('campaign.create', []));
        
        $result = $this->adapter->create($dto, false);
        
        expect($result->isInvalid())->toBeTrue();
    });

    it('handles provider errors', function () {
        $dto = $this->createTestCampaignDTO();
        $exception = new \RuntimeException('Provider error');
        $mappedException = new \DomainException('Mapped error');
        
        $this->createSpec->shouldReceive('preflight')->with($dto)->once();
        $this->createRequestMapper->shouldReceive('toCampaignsRequest')
            ->with($dto, false)
            ->andThrow($exception);
        $this->errorMapper->shouldReceive('map')->with($exception)->andReturn($mappedException);
        
        $result = $this->adapter->create($dto, false);
        
        expect($result->isErr())->toBeTrue()
            ->and($result->error())->toHaveKey('class', \DomainException::class);
    });

    it('creates campaign with budget on the fly', function () {
        $dto = $this->createTestCampaignDTO(['budgetResourceName' => null]);
        $budgetOp = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation::class);
        $unifiedRequest = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\MutateRequest::class);
        $response = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\MutateResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $googleAdsServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\GoogleAdsServiceClient::class);
        $expectedResult = $this->createTestCampaignDTO(['externalId' => '123']);
        
        $this->createSpec->shouldReceive('preflight')->with($dto)->once();
        $this->budgetRequestMapper->shouldReceive('toBudgetOperation')
            ->with($dto, -1)
            ->andReturn($budgetOp);
        $this->createRequestMapper->shouldReceive('toUnifiedRequest')
            ->with($dto, false, $budgetOp)
            ->andReturn($unifiedRequest);
        $this->client->shouldReceive('raw')->andReturn($googleAdsClient);
        $googleAdsClient->shouldReceive('getGoogleAdsServiceClient')->andReturn($googleAdsServiceClient);
        $googleAdsServiceClient->shouldReceive('mutate')->with($unifiedRequest)->andReturn($response);
        $this->logger->shouldReceive('info')->once();
        $this->createResponseMapper->shouldReceive('toCanonical')->with($response)->andReturn($expectedResult);
        
        $result = $this->adapter->create($dto, false);
        
        expect($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBe($expectedResult);
    });

    it('performs validate-only creation', function () {
        $dto = $this->createTestCampaignDTO();
        $request = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest::class);
        $response = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse::class);
        $googleAdsClient = Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
        $campaignServiceClient = Mockery::mock(\Google\Ads\GoogleAds\V21\Services\CampaignServiceClient::class);
        
        $this->createSpec->shouldReceive('preflight')->with($dto)->once();
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
});