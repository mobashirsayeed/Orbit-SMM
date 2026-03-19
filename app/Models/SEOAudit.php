<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOAudit extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'seo_monitor_id',
        'url',
        'status_code',
        'load_time',
        'page_size',
        'meta_tags',
        'headings',
        'links',
        'images',
        'issues',
        'score',
    ];

    protected $casts = [
        'meta_tags' => 'array',
        'headings' => 'array',
        'links' => 'array',
        'images' => 'array',
        'issues' => 'array',
    ];

    public function seoMonitor(): BelongsTo
    {
        return $this->belongsTo(SEOMonitor::class);
    }

    public function scopeByScore($query, $minScore)
    {
        return $query->where('score', '>=', $minScore);
    }

    public function scopeWithIssues($query)
    {
        return $query->whereJsonLength('issues', '>', 0);
    }

    public function getGradeAttribute(): string
    {
        return match (true) {
            $this->score >= 90 => 'A',
            $this->score >= 80 => 'B',
            $this->score >= 70 => 'C',
            $this->score >= 60 => 'D',
            default => 'F',
        };
    }

    public function getCriticalIssuesAttribute(): array
    {
        return collect($this->issues ?? [])
            ->where('severity', 'critical')
            ->toArray();
    }

    public function getWarningsAttribute(): array
    {
        return collect($this->issues ?? [])
            ->where('severity', 'warning')
            ->toArray();
    }
}
