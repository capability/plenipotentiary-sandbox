<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Services;

use Plenipotentiary\Laravel\Contracts\ApiCrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits\CreatesAdGroup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits\ReadsAdGroup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits\UpdatesAdGroup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits\DeletesAdGroup;

class AdGroupService implements ApiCrudServiceContract
{
    use CreatesAdGroup, ReadsAdGroup, UpdatesAdGroup, DeletesAdGroup;
}
