<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantIsSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = app('tenant');

        if (!$tenant) {
            return redirect()->route('login');
        }

        // Check subscription via Cashier's subscribed() method on the workspace
        if (method_exists($tenant, 'subscribed') && !$tenant->subscribed('default')) {
            return redirect()->route('billing.plans');
        }

        return $next($request);
    }
}
