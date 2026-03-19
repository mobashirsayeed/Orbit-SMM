<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Tenant is identified via the user's active workspace
            $workspace = $user->workspaces()->first();

            if ($workspace) {
                app()->instance('tenant', $workspace);
                return $next($request);
            }
        }

        // Allow routes that don't strictly need a tenant (auth routes, etc.)
        return $next($request);
    }
}
