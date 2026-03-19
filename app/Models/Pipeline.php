<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getWinRateAttribute(): float
    {
        $total = $this->deals()->whereIn('status', ['won', 'lost'])->count();
        $won = $this->deals()->where('status', 'won')->count();
        
        return $total > 0 ? round(($won / $total) * 100, 2) : 0;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->deals()->open()->sum('value') ?? 0;
    }
}
