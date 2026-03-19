<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Contact;
use App\Services\Inbox\InboxPollingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InboxController extends Controller
{
    public function __construct(
        private readonly InboxPollingService $pollingService
    ) {}

    public function index(Request $request)
    {
        $tenantId = app('tenant')->id;
        $userId = $request->user()->id;

        $query = Conversation::where('tenant_id', $tenantId)
            ->with(['contact', 'assignee', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('assigned')) {
            if ($request->assigned === 'me') {
                $query->where('assigned_to', $userId);
            } elseif ($request->assigned === 'unassigned') {
                $query->whereNull('assigned_to');
            }
        }

        if ($request->filled('search')) {
            $query->whereHas('contact', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $conversations = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Conversation::where('tenant_id', $tenantId)->count(),
            'open' => Conversation::where('tenant_id', $tenantId)->open()->count(),
            'unread' => Conversation::where('tenant_id', $tenantId)->unread()->count(),
            'assigned_to_me' => Conversation::where('tenant_id', $tenantId)
                ->where('assigned_to', $userId)
                ->open()
                ->count(),
        ];

        return Inertia::render('Inbox/Index', [
            'conversations' => $conversations,
            'stats' => $stats,
            'filters' => $request->only(['status', 'platform', 'assigned', 'search']),
            'pollingInterval' => config('orbit.polling_interval'),
        ]);
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load([
            'contact',
            'assignee',
            'messages' => function ($q) {
                $q->with(['user', 'contact', 'replies'])
                    ->orderBy('created_at', 'asc');
            },
            'notes' => function ($q) {
                $q->with('user')->latest();
            },
        ]);

        // Mark as read
        $conversation->markAllAsRead();

        return Inertia::render('Inbox/Show', [
            'conversation' => $conversation,
        ]);
    }

    public function poll(Request $request)
    {
        $tenantId = app('tenant')->id;
        $userId = $request->user()->id;

        $data = $this->pollingService->pollForNewMessages($tenantId);

        return response()->json($data);
    }

    public function unreadCount(Request $request)
    {
        $tenantId = app('tenant')->id;
        $userId = $request->user()->id;

        $count = $this->pollingService->getUnreadCount($tenantId, $userId);

        return response()->json(['count' => $count]);
    }

    public function sync(Request $request)
    {
        $tenantId = app('tenant')->id;
        $platform = $request->get('platform');

        $this->pollingService->triggerSync($tenantId, $platform);

        return back()->with('success', 'Sync initiated');
    }
}
