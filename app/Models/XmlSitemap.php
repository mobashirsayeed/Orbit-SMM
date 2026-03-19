<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class XmlSitemap extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'url', 'last_generated_at', 'url_count',
        'file_path', 'status',
    ];

    protected $casts = [
        'last_generated_at' => 'datetime',
    ];
}
