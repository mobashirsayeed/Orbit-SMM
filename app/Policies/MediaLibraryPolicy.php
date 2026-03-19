<?php

namespace App\Policies;

use App\Models\MediaLibrary;
use App\Models\User;

class MediaLibraryPolicy
{
    public function view(User $user, MediaLibrary $media): bool
    {
        return $media->tenant_id === app('tenant')->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MediaLibrary $media): bool
    {
        return $media->tenant_id === app('tenant')->id 
            && $user->isEditor();
    }

    public function delete(User $user, MediaLibrary $media): bool
    {
        return $media->tenant_id === app('tenant')->id 
            && $user->isEditor();
    }
}
