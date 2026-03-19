<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandVoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'tone',
        'instructions',
        'examples',
        'is_default',
    ];

    protected $casts = [
        'examples' => 'array',
        'is_default' => 'boolean',
    ];

    public function generations(): HasMany
    {
        return $this->hasMany(AIContentGeneration::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getSystemPromptAttribute(): string
    {
        $prompt = "You are an expert content creator for {$this->name}.";
        $prompt .= " Tone: {$this->tone}.";
        
        if ($this->instructions) {
            $prompt .= " {$this->instructions}";
        }

        return $prompt;
    }
}
