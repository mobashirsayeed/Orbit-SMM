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

class SyncFacebookMessagesJob implements ShouldQueue
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
            ->where('platform', 'facebook');

        if ($this->accountId) {
            $query->where('id', $this->accountId);
        }

        $accounts = $query->get();

        foreach ($accounts as $account) {
            try {
                $this->syncPageMessages($account);
                $account->update(['last_synced_at' => now()]);
            } catch (\Exception $e) {
                Log::error('Facebook message sync failed', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function syncPageMessages(SocialAccount $account): void
    {
        $pageId = $account->platform_account_id;
        $token = $account->token;

        // Get conversations
        $response = Http::get("https://graph.facebook.com/v18.0/{$pageId}/conversations", [
            'access_token' => $token,
            'fields' => 'id,participants,updated_time,messages{from,message,created_time,attachments}',
            'limit' => 50,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Facebook API error: ' . $response->body());
        }

        $conversations = $response->json()['data'] ?? [];

        foreach ($conversations as $fbConversation) {
            $this->processFacebookConversation($fbConversation, $account);
        }
    }

    private function processFacebookConversation(array $fbConversation, SocialAccount $account): void
    {
        $externalId = $fbConversation['id'];
        $participants = $fbConversation['participants']['data'] ?? [];

        // Find or create contact
        $contact = $this->findOrCreateContact($participants, $account->tenant_id);

        if (!$contact) {
            return;
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $account->tenant_id,
                'platform' => 'facebook',
                'external_id' => $externalId,
            ],
            [
                'contact_id' => $contact->id,
                'status' => 'open',
                'meta' => ['participants' => $participants],
            ]
        );

        // Sync messages
        $messages = $fbConversation['messages']['data'] ?? [];
        foreach ($messages as $fbMessage) {
            $this->processFacebookMessage($fbMessage, $conversation, $account, $contact);
        }
    }

    private function processFacebookMessage(array $fbMessage, Conversation $conversation, SocialAccount $account, Contact $contact): void
    {
        $externalId = $fbMessage['id'] ?? null;
        $fromId = $fbMessage['from']['id'] ?? null;

        // Skip if already synced
        if ($externalId && Message::where('tenant_id', $conversation->tenant_id)
            ->where('meta->external_id', $externalId)
            ->exists()) {
            return;
        }

        $isFromContact = $fromId && $fromId !== $account->platform_account_id;
        $direction = $isFromContact ? 'inbound' : 'outbound';

        Message::create([
            'tenant_id' => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'contact_id' => $isFromContact ? $contact->id : null,
            'user_id' => $isFromContact ? null : $account->tenant->owner_id,
            'body' => $fbMessage['message'] ?? '',
            'direction' => $direction,
            'status' => $isFromContact ? 'unread' : 'read',
            'received_at' => $fbMessage['created_time'] ?? now(),
            'meta' => ['external_id' => $externalId],
        ]);
    }

    private function findOrCreateContact(array $participants, int $tenantId): ?Contact
    {
        foreach ($participants as $participant) {
            if (!isset($participant['id'])) {
                continue;
            }

            $contact = Contact::where('tenant_id', $tenantId)
                ->where('meta->facebook_id', $participant['id'])
                ->first();

            if ($contact) {
                return $contact;
            }

            // Create new contact
            return Contact::create([
                'tenant_id' => $tenantId,
                'name' => $participant['name'] ?? 'Facebook User',
                'avatar_url' => $participant['picture'] ?? null,
                'source' => 'facebook',
                'meta' => ['facebook_id' => $participant['id']],
            ]);
        }

        return null;
    }
}
