<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboxMessage extends Model
{
    use HasFactory, BelongsToWorkspace, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'platform',
        'channel_type',
        'external_id',
        'sender_name',
        'sender_avatar',
        'sender_external_id',
        'body',
        'parent_id',
        'status',
        'direction',
        'assigned_to',
        'contact_id',
        'attachments',
        'meta',
        'received_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'meta' => 'array',
        'received_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(InboxMessage::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'parent_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function isUnread(): bool
    {
        return $this->status === 'unread';
    }

    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }

    public function markAsReplied(): void
    {
        $this->update(['status' => 'replied']);
    }

    public function scopeUnread($q)
    {
        return $q->where('status', 'unread');
    }

    public function scopeForPlatform($q, string $p)
    {
        return $q->where('platform', $p);
    }

    public function scopeInbound($q)
    {
        return $q->where('direction', 'inbound');
    }
}
