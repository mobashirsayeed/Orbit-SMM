<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentTemplate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'prompt_template',
        'category',
        'variables',
        'is_public',
        'uses_count',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_public' => 'boolean',
        'uses_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function incrementUses(): void
    {
        $this->increment('uses_count');
    }

    public function render(array $variables = []): string
    {
        $template = $this->prompt_template;

        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }
}
