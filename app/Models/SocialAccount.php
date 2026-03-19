<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SocialAccount extends Model
{
    use HasFactory, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'platform',
        'platform_account_id',
        'account_name',
        'account_avatar',
        'token',
        'token_secret',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'meta',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'scopes' => 'array',
        'meta' => 'array',
    ];

    public function setTokenAttribute(string $v): void
    {
        $this->attributes['token'] = Crypt::encryptString($v);
    }

    public function getTokenAttribute(?string $v): ?string
    {
        return $v ? Crypt::decryptString($v) : null;
    }

    public function setTokenSecretAttribute(?string $v): void
    {
        $this->attributes['token_secret'] = $v ? Crypt::encryptString($v) : null;
    }

    public function getTokenSecretAttribute(?string $v): ?string
    {
        return $v ? Crypt::decryptString($v) : null;
    }

    public function setRefreshTokenAttribute(?string $v): void
    {
        $this->attributes['refresh_token'] = $v ? Crypt::encryptString($v) : null;
    }

    public function getRefreshTokenAttribute(?string $v): ?string
    {
        return $v ? Crypt::decryptString($v) : null;
    }

    public function scopeForPlatform($q, string $p)
    {
        return $q->where('platform', $p);
    }
}
