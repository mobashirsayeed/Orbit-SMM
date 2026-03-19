<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'platform',
        'external_id',
        'subject',
        'status',
        'assigned_to',
        'meta',
        'unread_count',
        'last_message_at',
        'is_starred',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_message_at' => 'datetime',
        'is_starred' => 'boolean',
        'unread_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            if (!$conversation->last_message_at) {
                $conversation->last_message_at = now();
            }
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MessageNote::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeWithLatestMessage($query)
    {
        return $query->with(['messages' => function ($q) {
            $q->latest()->limit(1);
        }]);
    }

    public function markAllAsRead(): void
    {
        $this->messages()->unread()->update(['status' => 'read', 'read_at' => now()]);
        $this->update(['unread_count' => 0]);
    }

    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    public function reopen(): void
    {
        $this->update(['status' => 'open']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function assignTo(?int $userId): void
    {
        $this->update(['assigned_to' => $userId]);
    }

    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }

    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }
}
