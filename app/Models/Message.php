<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'user_id',
        'contact_id',
        'body',
        'direction',
        'status',
        'attachments',
        'meta',
        'received_at',
        'is_starred',
        'is_internal',
        'parent_id',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'meta' => 'array',
        'received_at' => 'datetime',
        'read_at' => 'datetime',
        'is_starred' => 'boolean',
        'is_internal' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (!$message->received_at) {
                $message->received_at = now();
            }
        });

        static::created(function ($message) {
            if ($message->isInbound()) {
                $message->conversation->increment('unread_count');
                $message->conversation->update(['last_message_at' => now()]);
            }
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MessageNote::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function isUnread(): bool
    {
        return $this->status === 'unread';
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeWithReplies($query)
    {
        return $query->with('replies');
    }
}
