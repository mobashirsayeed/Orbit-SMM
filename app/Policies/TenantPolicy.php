<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenant_id', $tenant->id)->exists();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->tenants()
            ->where('tenant_id', $tenant->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->tenants()
            ->where('tenant_id', $tenant->id)
            ->wherePivot('role', 'admin')
            ->exists()
            && $tenant->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        $currentPlan = config('plans.' . $user->currentTenant()?->plan ?? 'starter');
        $workspaceCount = $user->tenants()->count();
        
        return $workspaceCount < $currentPlan['limits']['workspaces'];
    }
}
