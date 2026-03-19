<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, BelongsToWorkspace, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'created_by',
        'body',
        'media_urls',
        'platforms',
        'status',
        'scheduled_at',
        'published_at',
        'platform_results',
        'meta',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'platforms' => 'array',
        'platform_results' => 'array',
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function scopeScheduled($q)
    {
        return $q->where('status', 'scheduled');
    }

    public function scopeDue($q)
    {
        return $q->scheduled()->where('scheduled_at', '<=', now());
    }
}
