<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class SEOMonitor extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'url', 'keyword', 'current_rank', 'previous_rank',
        'search_engine', 'location', 'device', 'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];
}
