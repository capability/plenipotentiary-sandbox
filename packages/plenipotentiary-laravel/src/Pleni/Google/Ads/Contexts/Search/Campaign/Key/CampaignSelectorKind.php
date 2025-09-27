<?php

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key;

enum CampaignSelectorKind: string
{
    case ResourceName;
    case ExternalId;
    case LocalId;
}
