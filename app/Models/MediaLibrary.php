<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaLibrary extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'disk',
        'path',
        'url',
        'width',
        'height',
        'tags',
        'folder',
        'meta',
    ];

    protected $casts = [
        'tags' => 'array',
        'meta' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;

        return $query->where(function ($query) use ($search) {
            $query->where('original_filename', 'like', "%{$search}%")
                ->orWhere('filename', 'like', "%{$search}%");
        });
    }

    public function scopeInFolder($query, ?string $folder)
    {
        if (!$folder) return $query;

        return $query->where('folder', $folder);
    }

    public function scopeByType($query, ?string $type)
    {
        if (!$type) return $query;

        return $query->where('mime_type', 'like', "{$type}%");
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
