<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Policies\MediaLibraryPolicy;
use App\Policies\TenantPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        MediaLibrary::class => MediaLibraryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
