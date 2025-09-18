<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Services;

use Plenipotentiary\Laravel\Contracts\ApiCrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits\CreatesAd;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits\ReadsAd;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits\UpdatesAd;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits\DeletesAd;

class AdService implements ApiCrudServiceContract
{
    use CreatesAd, ReadsAd, UpdatesAd, DeletesAd;
}
