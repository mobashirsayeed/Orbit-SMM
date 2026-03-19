<?php

namespace App\Services\Inbox;

use App\Models\SocialAccount;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncLinkedInMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly int $tenantId,
        private readonly ?int $accountId = null
    ) {}

    public function handle(): void
    {
        $query = SocialAccount::where('tenant_id', $this->tenantId)
            ->where('platform', 'linkedin');

        if ($this->accountId) {
            $query->where('id', $this->accountId);
        }

        $accounts = $query->get();

        foreach ($accounts as $account) {
            try {
                $this->syncLinkedInMessages($account);
                $account->update(['last_synced_at' => now()]);
            } catch (\Exception $e) {
                Log::error('LinkedIn message sync failed', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function syncLinkedInMessages(SocialAccount $account): void
    {
        $token = $account->token;

        // Get conversations (LinkedIn API v2)
        $response = Http::withToken($token)
            ->get('https://api.linkedin.com/v2/messages', [
                'q' => 'members',
                'members' => "urn:li:person:{$account->platform_account_id}",
                'count' => 50,
            ]);

        if (!$response->successful()) {
            throw new \Exception('LinkedIn API error: ' . $response->body());
        }

        $conversations = $response->json()['elements'] ?? [];

        foreach ($conversations as $conversation) {
            $this->processLinkedInConversation($conversation, $account);
        }
    }

    private function processLinkedInConversation(array $conversation, SocialAccount $account): void
    {
        $conversationId = $conversation['id'] ?? null;
        if (!$conversationId) {
            return;
        }

        // Get messages for this conversation
        $response = Http::withToken($account->token)
            ->get("https://api.linkedin.com/v2/messages/{$conversationId}/messages", [
                'count' => 50,
            ]);

        if (!$response->successful()) {
            return;
        }

        $messages = $response->json()['elements'] ?? [];

        foreach ($messages as $message) {
            $this->processLinkedInMessage($message, $conversation, $account);
        }
    }

    private function processLinkedInMessage(array $message, array $conversation, SocialAccount $account): void
    {
        $from = $message['from'] ?? null;
        $text = $message['body'] ?? '';
        $createdAt = $message['createdAt'] ?? now();

        if (!$from || !$text) {
            return;
        }

        $isFromContact = !str_contains($from, $account->platform_account_id);

        // Extract contact ID from URN
        $contactId = str_replace('urn:li:person:', '', $from);

        // Find or create contact
        $contact = Contact::firstOrCreate(
            [
                'tenant_id' => $account->tenant_id,
                'meta->linkedin_id' => $contactId,
            ],
            [
                'name' => 'LinkedIn User',
                'source' => 'linkedin',
                'meta' => ['linkedin_id' => $contactId],
            ]
        );

        // Find or create conversation
        $conv = Conversation::firstOrCreate(
            [
                'tenant_id' => $account->tenant_id,
                'platform' => 'linkedin',
                'external_id' => $conversation['id'],
            ],
            [
                'contact_id' => $contact->id,
                'status' => 'open',
            ]
        );

        // Create message
        Message::create([
            'tenant_id' => $account->tenant_id,
            'conversation_id' => $conv->id,
            'contact_id' => $isFromContact ? $contact->id : null,
            'user_id' => $isFromContact ? null : $account->tenant->owner_id,
            'body' => $text,
            'direction' => $isFromContact ? 'inbound' : 'outbound',
            'status' => $isFromContact ? 'unread' : 'read',
            'received_at' => $createdAt,
            'meta' => ['external_id' => $message['id']],
        ]);
    }
}
