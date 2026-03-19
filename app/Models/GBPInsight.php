<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class GBPInsight extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'gbp_location_id', 'metric_type', 'value',
        'period_start', 'period_end', 'data',
    ];

    protected $casts = [
        'data'         => 'array',
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function location()
    {
        return $this->belongsTo(GBPLocation::class, 'gbp_location_id');
    }
}
