<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToWorkspace;

class SocialPost extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'social_account_id', 'platform', 'content',
        'media_urls', 'scheduled_at', 'published_at', 'status',
        'external_id', 'error_message',
    ];

    protected $casts = [
        'media_urls'   => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }
}
