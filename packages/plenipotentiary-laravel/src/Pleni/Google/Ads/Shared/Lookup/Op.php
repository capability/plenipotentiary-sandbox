<?php

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup;

enum Op { case Eq; case In; case NotIn; case Like; case StartsWith; case Between; }
