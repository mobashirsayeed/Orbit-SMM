<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSnapshot extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'platform',
        'metrics',
        'snapshot_date',
    ];

    protected $casts = [
        'metrics' => 'array',
        'snapshot_date' => 'date',
    ];

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('snapshot_date', [$startDate, $endDate]);
    }

    public function getMetricAttribute(string $key): mixed
    {
        return $this->metrics[$key] ?? null;
    }
}
