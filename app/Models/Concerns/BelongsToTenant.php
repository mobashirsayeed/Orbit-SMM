<?php

namespace App\Models\Concerns;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (app()->bound('tenant') && $tenant = app('tenant')) {
                $model->tenant_id = $tenant->id;
            }
        });

        static::addGlobalScope('tenant', function ($builder) {
            if (app()->bound('tenant') && $tenant = app('tenant')) {
                $builder->where('tenant_id', $tenant->id);
            }
        });
    }
}
