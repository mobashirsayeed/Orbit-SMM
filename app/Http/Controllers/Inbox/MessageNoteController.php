<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\MessageNote;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageNoteController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        MessageNote::create([
            'tenant_id' => app('tenant')->id,
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Note added');
    }

    public function update(Request $request, MessageNote $note)
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $note->update($validated);

        return back()->with('success', 'Note updated');
    }

    public function destroy(MessageNote $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('success', 'Note deleted');
    }
}
