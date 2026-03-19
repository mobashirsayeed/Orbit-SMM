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

class SyncTwitterMessagesJob implements ShouldQueue
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
            ->where('platform', 'twitter');

        if ($this->accountId) {
            $query->where('id', $this->accountId);
        }

        $accounts = $query->get();

        foreach ($accounts as $account) {
            try {
                $this->syncDirectMessages($account);
                $account->update(['last_synced_at' => now()]);
            } catch (\Exception $e) {
                Log::error('Twitter message sync failed', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function syncDirectMessages(SocialAccount $account): void
    {
        $token = $account->token;

        // Get DM events
        $response = Http::withToken($token)
            ->get('https://api.twitter.com/2/dm_events', [
                'dm_conversation.id' => $account->platform_account_id,
                'max_results' => 50,
                'event_types' => 'MessageCreate',
            ]);

        if (!$response->successful()) {
            throw new \Exception('Twitter API error: ' . $response->body());
        }

        $events = $response->json()['data'] ?? [];
        $includes = $response->json()['includes'] ?? [];
        $users = collect($includes['users'] ?? [])->keyBy('id');

        foreach ($events as $event) {
            $this->processTwitterDM($event, $users, $account);
        }
    }

    private function processTwitterDM(array $event, $users, SocialAccount $account): void
    {
        $senderId = $event['sender_id'] ?? null;
        $receiverId = $event['dm_conversation']['dm_conversation_id'] ?? null;
        $text = $event['message_create']['message_data']['text'] ?? '';
        $createdAt = $event['created_at'] ?? now();

        $isFromContact = $senderId !== $account->platform_account_id;

        // Find or create contact
        $contact = Contact::firstOrCreate(
            [
                'tenant_id' => $account->tenant_id,
                'meta->twitter_id' => $senderId,
            ],
            [
                'name' => $users[$senderId]['name'] ?? 'Twitter User',
                'avatar_url' => $users[$senderId]['profile_image_url'] ?? null,
                'source' => 'twitter',
                'meta' => ['twitter_id' => $senderId],
            ]
        );

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $account->tenant_id,
                'platform' => 'twitter',
                'external_id' => $event['id'],
            ],
            [
                'contact_id' => $contact->id,
                'status' => 'open',
            ]
        );

        // Create message
        Message::create([
            'tenant_id' => $account->tenant_id,
            'conversation_id' => $conversation->id,
            'contact_id' => $isFromContact ? $contact->id : null,
            'user_id' => $isFromContact ? null : $account->tenant->owner_id,
            'body' => $text,
            'direction' => $isFromContact ? 'inbound' : 'outbound',
            'status' => $isFromContact ? 'unread' : 'read',
            'received_at' => $createdAt,
            'meta' => ['external_id' => $event['id']],
        ]);
    }
}
