<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'channel',
        'notification_type',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function isEnabled($userId, $channel, $notificationType, $tenantId = null): bool
    {
        $preference = static::where('user_id', $userId)
            ->where('channel', $channel)
            ->where('notification_type', $notificationType)
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id')
                    ->orWhere('tenant_id', $tenantId);
            })
            ->first();

        return $preference ? $preference->enabled : true; // Default to enabled
    }
}
