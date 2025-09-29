<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Spec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException;

describe('Create Spec', function () {
    it('validates valid campaign data', function () {
        $spec = new Spec;
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Valid Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        expect(fn () => $spec->preflight($dto))->not->toThrow();
    });

    it('validates name is required and not too long', function () {
        $spec = new Spec;

        // Missing name
        $dto1 = CampaignCanonicalDTO::fromArray([
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        expect(fn () => $spec->preflight($dto1))
            ->toThrow(ValidationException::class);

        // Name too long
        $dto2 = CampaignCanonicalDTO::fromArray([
            'name' => str_repeat('a', 129),
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        expect(fn () => $spec->preflight($dto2))
            ->toThrow(ValidationException::class);
    });

    it('validates status enum', function () {
        $spec = new Spec;
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'INVALID_STATUS',
            'budgetResourceName' => 'customers/123/budgets/456',
        ]);

        expect(fn () => $spec->preflight($dto))
            ->toThrow(ValidationException::class);
    });

    it('validates budget resource name is required', function () {
        $spec = new Spec;
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
        ]);

        expect(fn () => $spec->preflight($dto))
            ->toThrow(ValidationException::class);
    });

    it('describes validation rules', function () {
        $spec = new Spec;
        $description = $spec->describe();

        expect($description->operation)->toBe('campaign.create')
            ->and($description->rules)->toHaveCount(3)
            ->and($description->rules[0])->toHaveKey('field', 'name')
            ->and($description->rules[1])->toHaveKey('field', 'status')
            ->and($description->rules[2])->toHaveKey('field', 'budgetResourceName');
    });
});
