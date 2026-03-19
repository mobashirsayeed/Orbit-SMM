<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class AnalyticsReport extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'name', 'type', 'date_from', 'date_to',
        'platforms', 'metrics', 'generated_at', 'file_path',
    ];

    protected $casts = [
        'platforms'    => 'array',
        'metrics'      => 'array',
        'date_from'    => 'date',
        'date_to'      => 'date',
        'generated_at' => 'datetime',
    ];
}
