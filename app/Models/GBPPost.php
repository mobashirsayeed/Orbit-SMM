<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GBPPost extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'post_id',
        'post_type',
        'headline',
        'content',
        'cta_type',
        'cta_url',
        'media_urls',
        'publish_at',
        'expire_at',
        'status',
        'metrics',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'metrics' => 'array',
        'publish_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GBPLocation::class, 'location_id');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('publish_at', '<=', now());
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function isExpired(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }
}
