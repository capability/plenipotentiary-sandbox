<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    protected function hosts(): array
    {
        if (app()->environment('local')) {
            // In local, don’t restrict hosts (skip specifying patterns)
            return [];
        }

        // In non-local, trust the APP_URL host (+ subdomains)
        return [$this->allSubdomainsOfApplicationUrl()];
    }
}
