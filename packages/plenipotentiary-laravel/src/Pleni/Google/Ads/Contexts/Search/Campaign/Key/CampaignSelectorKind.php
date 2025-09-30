<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key;

enum CampaignSelectorKind: string
{
    case ResourceName = 'resource_name';
    case ExternalId = 'external_id';
    case LocalId = 'local_id';
}
