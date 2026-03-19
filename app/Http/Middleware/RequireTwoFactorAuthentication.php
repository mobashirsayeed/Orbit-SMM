<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->hasTwoFactorEnabled()) {
            if (!session()->get('2fa_verified')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
