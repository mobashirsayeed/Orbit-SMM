<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GBPReview extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'review_id',
        'reviewer_name',
        'reviewer_avatar',
        'rating',
        'comment',
        'review_date',
        'reply',
        'reply_date',
        'sentiment_score',
        'is_responded',
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'reply_date' => 'datetime',
        'is_responded' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GBPLocation::class, 'location_id');
    }

    public function scopeUnresponded($query)
    {
        return $query->where('is_responded', false);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('review_date', '>=', now()->subDays($days));
    }

    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }
}
