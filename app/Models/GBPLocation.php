<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GBPLocation extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'place_id',
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'website',
        'latitude',
        'longitude',
        'categories',
        'hours',
        'attributes',
        'last_synced_at',
    ];

    protected $casts = [
        'categories' => 'array',
        'hours' => 'array',
        'attributes' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_synced_at' => 'datetime',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(GBPPost::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GBPReview::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(GBPInsight::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(GBPProduct::class);
    }

    public function getUnrespondedReviewsCountAttribute(): int
    {
        return $this->reviews()->where('is_responded', false)->count();
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]));
    }
}
