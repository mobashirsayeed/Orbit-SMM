<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemaMarkup extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'type',
        'schema_data',
        'page_url',
        'is_active',
    ];

    protected $casts = [
        'schema_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function getJsonLdAttribute(): string
    {
        return '<script type="application/ld+json">' . 
            json_encode($this->schema_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . 
            '</script>';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
