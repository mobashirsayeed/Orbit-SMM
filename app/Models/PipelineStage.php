<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'color',
        'order',
        'probability',
    ];

    protected $casts = [
        'order' => 'integer',
        'probability' => 'integer',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getDealsCountAttribute(): int
    {
        return $this->deals()->open()->count();
    }

    public function getDealsValueAttribute(): float
    {
        return $this->deals()->open()->sum('value') ?? 0;
    }
}
