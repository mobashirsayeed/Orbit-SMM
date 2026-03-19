<?php

namespace App\Models\Concerns;

use App\Models\Scopes\WorkspaceScope;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToWorkspace
{
    protected static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope());
        static::creating(function ($model) {
            if (!$model->workspace_id && app()->has('workspace')) {
                $model->workspace_id = app('workspace')->id;
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
