<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'plan',
        'requires_approval',
        'settings',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Workspace $w) {
            $w->slug = $w->slug ?: Str::slug($w->name) . '-' . Str::random(6);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    public function userRole(User $user): ?string
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->first()?->pivot?->role;
    }
}
