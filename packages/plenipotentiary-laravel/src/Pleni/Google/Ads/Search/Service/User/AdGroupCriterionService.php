<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User;

use Plenipotentiary\Laravel\Contracts\ApiCrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroupCriterion\Traits\CreatesAdGroupCriterion;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroupCriterion\Traits\ReadsAdGroupCriterion;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroupCriterion\Traits\UpdatesAdGroupCriterion;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroupCriterion\Traits\DeletesAdGroupCriterion;

/**
 * Thin AdGroupCriterionService implementing ApiCrudServiceContract.
 * Delegates operations to heavy Traits.
 */
class AdGroupCriterionService implements ApiCrudServiceContract
{
    use CreatesAdGroupCriterion, ReadsAdGroupCriterion, UpdatesAdGroupCriterion, DeletesAdGroupCriterion;
}
