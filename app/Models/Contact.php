<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class Contact extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'name', 'email', 'phone', 'company', 
        'social_platform', 'social_id', 'avatar_url', 'notes', 'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
