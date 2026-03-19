<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordRanking extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'seo_monitor_id',
        'keyword',
        'search_engine',
        'location',
        'position',
        'previous_position',
        'url',
        'tracked_date',
        'serp_features',
    ];

    protected $casts = [
        'tracked_date' => 'date',
        'serp_features' => 'array',
        'position' => 'integer',
        'previous_position' => 'integer',
    ];

    public function seoMonitor(): BelongsTo
    {
        return $this->belongsTo(SEOMonitor::class);
    }

    public function scopeByKeyword($query, string $keyword)
    {
        return $query->where('keyword', $keyword);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tracked_date', [$startDate, $endDate]);
    }

    public function scopeTopPositions($query, int $position = 10)
    {
        return $query->where('position', '<=', $position);
    }

    public function getPositionChangeAttribute(): int
    {
        if (!$this->previous_position) {
            return 0;
        }
        return $this->previous_position - $this->position; // Positive = improvement
    }

    public function getIsImprovingAttribute(): bool
    {
        return $this->position_change > 0;
    }

    public function getIsDecliningAttribute(): bool
    {
        return $this->position_change < 0;
    }
}
