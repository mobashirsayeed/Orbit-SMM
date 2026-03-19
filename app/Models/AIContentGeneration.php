<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class AIContentGeneration extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $table = 'ai_content_generations';

    protected $fillable = [
        'workspace_id', 'user_id', 'type', 'prompt', 'result',
        'model', 'tokens_used', 'platform', 'brand_voice_id',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
