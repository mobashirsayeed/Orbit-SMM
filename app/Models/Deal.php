<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'pipeline_id',
        'stage_id',
        'contact_id',
        'user_id',
        'title',
        'description',
        'value',
        'currency',
        'expected_close_date',
        'closed_date',
        'status',
        'lost_reason',
        'custom_fields',
        'meta',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'closed_date' => 'date',
        'custom_fields' => 'array',
        'meta' => 'array',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeOverdue($query)
    {
        return $query->open()
            ->where('expected_close_date', '<', now());
    }

    public function win(): void
    {
        $this->update([
            'status' => 'won',
            'closed_date' => now(),
        ]);
    }

    public function lose(string $reason): void
    {
        $this->update([
            'status' => 'lost',
            'lost_reason' => $reason,
            'closed_date' => now(),
        ]);
    }

    public function moveToStage(PipelineStage $stage): void
    {
        $this->update([
            'stage_id' => $stage->id,
        ]);
    }

    public function getWeightedValueAttribute(): float
    {
        $probability = $this->stage?->probability ?? 0;
        return ($this->value ?? 0) * ($probability / 100);
    }
}
