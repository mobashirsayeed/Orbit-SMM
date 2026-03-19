<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/team.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/social.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/inbox.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/analytics.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/ai.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/seo.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/gbp.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/crm.php'));
        });
    }
}
