<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplyTemplate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'content',
        'shortcut',
        'platforms',
        'is_public',
    ];

    protected $casts = [
        'platforms' => 'array',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeForPlatform($query, $platform)
    {
        return $query->where(function ($q) use ($platform) {
            $q->whereNull('platforms')
                ->orWhereJsonContains('platforms', $platform);
        });
    }

    public function scopeByShortcut($query, $shortcut)
    {
        return $query->where('shortcut', $shortcut);
    }
}
