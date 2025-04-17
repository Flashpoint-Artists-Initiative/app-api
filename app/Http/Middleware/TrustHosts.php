<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;
use Illuminate\Support\Facades\Log;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     *
     * @codeCoverageIgnore
     */
    public function hosts(): array
    {
        Log::info('subdomains: ' . $this->allSubdomainsOfApplicationUrl());
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
