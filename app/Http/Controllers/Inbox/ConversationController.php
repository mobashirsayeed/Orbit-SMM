<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function assign(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        $conversation->assignTo($validated['user_id'] ?? null);

        return back()->with('success', 'Conversation assigned');
    }

    public function close(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $conversation->close();

        return back()->with('success', 'Conversation closed');
    }

    public function reopen(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $conversation->reopen();

        return back()->with('success', 'Conversation reopened');
    }

    public function archive(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $conversation->archive();

        return back()->with('success', 'Conversation archived');
    }

    public function toggleStar(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $conversation->toggleStar();

        return back()->with('success', 'Conversation starred');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'exists:conversations,id',
            'action' => 'required|in:close,archive,assign,mark_read',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $conversations = Conversation::whereIn('id', $validated['conversation_ids'])
            ->where('tenant_id', app('tenant')->id)
            ->get();

        foreach ($conversations as $conversation) {
            $this->authorize('update', $conversation);

            match ($validated['action']) {
                'close' => $conversation->close(),
                'archive' => $conversation->archive(),
                'assign' => $conversation->assignTo($validated['user_id'] ?? null),
                'mark_read' => $conversation->markAllAsRead(),
            };
        }

        return back()->with('success', 'Conversations updated');
    }
}
