<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HashtagSet extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'hashtags',
        'category',
        'uses_count',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'uses_count' => 'integer',
    ];

    public function incrementUses(): void
    {
        $this->increment('uses_count');
    }

    public function getHashtagStringAttribute(): string
    {
        return implode(' ', array_map(fn($tag) => "#{$tag}", $this->hashtags));
    }
}
