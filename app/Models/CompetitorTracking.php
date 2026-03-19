<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorTracking extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'seo_monitor_id',
        'domain',
        'name',
        'keywords',
        'avg_position',
        'visibility_score',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function seoMonitor(): BelongsTo
    {
        return $this->belongsTo(SEOMonitor::class);
    }

    public function scopeByVisibility($query, $minVisibility)
    {
        return $query->where('visibility_score', '>=', $minVisibility);
    }
}
