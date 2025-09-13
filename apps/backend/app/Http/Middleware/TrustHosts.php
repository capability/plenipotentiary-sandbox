<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * When developing or running tests, we allow all hosts (empty array).
     * In other envs, trust the app URL's domain and its subdomains.
     */
    public function hosts(): array
    {
        if (app()->environment(['local', 'testing'])) {
            return [];
        }

        return [
            $this->allSubdomainsOfApplicationUrl(), // e.g. *.your-app-domain.tld
            // You can add explicit hosts if you need:
            // 'your-app-domain.tld',
        ];
    }
}
