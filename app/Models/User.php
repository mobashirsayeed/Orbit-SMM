<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_workspace_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function currentWorkspace(): ?Workspace
    {
        if ($this->current_workspace_id) {
            return Workspace::find($this->current_workspace_id);
        }
        return $this->workspaces()->first();
    }

    public function switchWorkspace(Workspace $workspace): void
    {
        if ($this->workspaces()->where('workspace_id', $workspace->id)->exists()) {
            $this->update(['current_workspace_id' => $workspace->id]);
        }
    }

    public function currentRole(): ?string
    {
        return $this->currentWorkspace()?->userRole($this);
    }

    public function isAdmin(): bool
    {
        return $this->currentRole() === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->currentRole(), ['admin', 'editor']);
    }
}
