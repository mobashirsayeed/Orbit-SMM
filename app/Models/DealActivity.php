<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealActivity extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'deal_id',
        'user_id',
        'type',
        'subject',
        'description',
        'scheduled_at',
        'completed_at',
        'is_completed',
        'meta',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'meta' => 'array',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeOverdue($query)
    {
        return $query->pending()
            ->where('scheduled_at', '<', now());
    }

    public function complete(): void
    {
        $this->update(['is_completed' => true, 'completed_at' => now()]);
    }
}
