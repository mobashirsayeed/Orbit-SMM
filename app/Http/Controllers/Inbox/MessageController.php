<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Social\PublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function __construct(
        private readonly PublisherService $publisherService
    ) {}

    public function store(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $attachmentUrls = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('inbox/' . app('tenant')->id, 'public');
                $attachmentUrls[] = Storage::url($path);
            }
        }

        $message = Message::create([
            'tenant_id' => app('tenant')->id,
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'contact_id' => $conversation->contact_id,
            'body' => $validated['body'],
            'direction' => 'outbound',
            'status' => 'read',
            'attachments' => $attachmentUrls,
            'meta' => $validated['attachments'] ?? [],
        ]);

        // Publish to social platform if applicable
        if (in_array($conversation->platform, ['facebook', 'twitter', 'linkedin'])) {
            $this->publishToPlatform($message, $conversation);
        }

        return back()->with('success', 'Message sent');
    }

    public function update(Request $request, Message $message)
    {
        $this->authorize('update', $message);

        $validated = $request->validate([
            'body' => 'sometimes|required|string',
            'is_starred' => 'sometimes|boolean',
            'status' => 'sometimes|in:unread,read,replied',
        ]);

        $message->update($validated);

        return back()->with('success', 'Message updated');
    }

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);

        $message->delete();

        return back()->with('success', 'Message deleted');
    }

    public function markAsRead(Message $message)
    {
        $this->authorize('update', $message);

        $message->markAsRead();

        return back();
    }

    public function toggleStar(Message $message)
    {
        $this->authorize('update', $message);

        $message->toggleStar();

        return back();
    }

    private function publishToPlatform(Message $message, Conversation $conversation): void
    {
        // Get social account for this platform
        $account = $conversation->contact->tenant->socialAccounts()
            ->where('platform', $conversation->platform)
            ->first();

        if (!$account) {
            return;
        }

        // Publish reply to platform
        match ($conversation->platform) {
            'facebook' => $this->publisherService->publishToFacebook(
                $this->createPostFromMessage($message),
                $account
            ),
            'twitter' => $this->publisherService->publishToTwitter(
                $this->createPostFromMessage($message),
                $account
            ),
            'linkedin' => $this->publisherService->publishToLinkedIn(
                $this->createPostFromMessage($message),
                $account
            ),
        };
    }

    private function createPostFromMessage(Message $message): \App\Models\SocialPost
    {
        return new \App\Models\SocialPost([
            'tenant_id' => $message->tenant_id,
            'body' => $message->body,
            'media_urls' => $message->attachments,
            'platforms' => [$message->conversation->platform],
        ]);
    }
}
