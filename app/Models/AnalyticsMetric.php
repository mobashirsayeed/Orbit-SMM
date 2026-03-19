<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsMetric extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'social_account_id',
        'metric_type',
        'value',
        'change',
        'metric_date',
        'meta',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'meta' => 'array',
        'value' => 'integer',
        'change' => 'integer',
    ];

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->whereHas('socialAccount', function ($q) use ($platform) {
            $q->where('platform', $platform);
        });
    }

    public function getPercentageChangeAttribute(): float
    {
        if ($this->value === 0) {
            return 0;
        }
        return round(($this->change / $this->value) * 100, 2);
    }
}
