<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // return $request->expectsJson() ? null : route('auth.show-login');

        if (!$request->expectsJson()) {
            $prefix = $request->segment(1);
    
            return match ($prefix) {
                'admin' => route('admin-login'),
                default => route('auth.show-login'),
            };
        }

        return null;
    }
}
