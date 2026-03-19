<?php

namespace App\Services\Inbox;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\Inbox\SyncFacebookMessagesJob;
use App\Services\Inbox\SyncTwitterMessagesJob;
use App\Services\Inbox\SyncLinkedInMessagesJob;

class InboxPollingService
{
    public function pollForNewMessages(int $tenantId): array
    {
        $newMessages = [];
        $updatedConversations = [];

        // Get conversations with new messages since last poll
        $conversations = Conversation::where('tenant_id', $tenantId)
            ->open()
            ->with(['messages' => function ($q) {
                $q->where('created_at', '>', now()->subSeconds(10))
                    ->orderBy('created_at', 'desc');
            }, 'contact', 'assignee'])
            ->get();

        foreach ($conversations as $conversation) {
            if ($conversation->messages->count() > 0) {
                $newMessages[] = [
                    'conversation_id' => $conversation->id,
                    'messages' => $conversation->messages->map(fn($m) => [
                        'id' => $m->id,
                        'body' => $m->body,
                        'direction' => $m->direction,
                        'created_at' => $m->created_at->toIso8601String(),
                        'user' => $m->user?->name,
                        'contact' => $m->contact?->name,
                    ]),
                ];

                $updatedConversations[] = [
                    'id' => $conversation->id,
                    'unread_count' => $conversation->unread_count,
                    'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                ];
            }
        }

        return [
            'new_messages' => $newMessages,
            'updated_conversations' => $updatedConversations,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function triggerSync(int $tenantId, ?string $platform = null): void
    {
        match ($platform) {
            'facebook' => SyncFacebookMessagesJob::dispatch($tenantId),
            'twitter' => SyncTwitterMessagesJob::dispatch($tenantId),
            'linkedin' => SyncLinkedInMessagesJob::dispatch($tenantId),
            default => [
                SyncFacebookMessagesJob::dispatch($tenantId),
                SyncTwitterMessagesJob::dispatch($tenantId),
                SyncLinkedInMessagesJob::dispatch($tenantId),
            ],
        };
    }

    public function getUnreadCount(int $tenantId, ?int $userId = null): int
    {
        $query = Conversation::where('tenant_id', $tenantId)
            ->where('unread_count', '>', 0);

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('assigned_to')
                    ->orWhere('assigned_to', $userId);
            });
        }

        return $query->sum('unread_count');
    }
}
