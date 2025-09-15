<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdGroupCriterionValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdGroupValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\CampaignValidator;

it('validates campaign requires name/budget/customerId', function () {
    $dto = new CampaignDomainData(1, '', '', null, null, null, null, null);
    expect(fn () => CampaignValidator::validateForCreate($dto))
        ->toThrow(RuntimeException::class);
});

it('validates adgroup requires name and campaign reference', function () {
    $dto = new AdGroupDomainData(1, '', 'ENABLED');
    expect(fn () => AdGroupValidator::validateForCreate($dto))
        ->toThrow(RuntimeException::class);
});

it('validates ad requires parent adgroup link', function () {
    $dto = new AdDomainData(1, null, null, null);
    expect(fn () => AdValidator::validateForCreate($dto))
        ->toThrow(RuntimeException::class);
});

it('validates adgroup criterion requires keyword and match type', function () {
    $dto = new AdGroupCriterionDomainData(1, '', '', '');
    expect(fn () => AdGroupCriterionValidator::validateForCreate($dto))
        ->toThrow(RuntimeException::class);
});
