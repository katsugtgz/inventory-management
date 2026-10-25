<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    /**
     * Livewire per-IP login throttling (5/minute) stalls e2e suites that
     * authenticate once per test from a single IP. Skip it under testing.
     */
    protected function rateLimit($maxAttempts, $decaySeconds = 60, $method = null, $component = null): void
    {
        if (! app()->environment('testing')) {
            parent::rateLimit($maxAttempts, $decaySeconds, $method, $component);
        }
    }
}
