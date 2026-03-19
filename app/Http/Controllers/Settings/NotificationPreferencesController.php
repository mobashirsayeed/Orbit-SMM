<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationPreferencesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = app('tenant')->id;

        $preferences = NotificationPreference::where('user_id', $user->id)
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id')
                    ->orWhere('tenant_id', $tenantId);
            })
            ->get()
            ->groupBy('channel');

        $notificationTypes = [
            'email' => [
                'post_published' => 'Post Published',
                'post_failed' => 'Post Failed',
                'message_received' => 'New Message',
                'approval_request' => 'Approval Request',
                'report_ready' => 'Report Ready',
            ],
            'in_app' => [
                'post_published' => 'Post Published',
                'post_failed' => 'Post Failed',
                'message_received' => 'New Message',
                'approval_request' => 'Approval Request',
                'mention' => 'Mention',
            ],
        ];

        return Inertia::render('Settings/NotificationPreferences', [
            'preferences' => $preferences,
            'notificationTypes' => $notificationTypes,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $tenantId = app('tenant')->id;

        foreach ($request->preferences as $channel => $types) {
            foreach ($types as $type => $enabled) {
                NotificationPreference::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'channel' => $channel,
                        'notification_type' => $type,
                        'tenant_id' => $tenantId,
                    ],
                    ['enabled' => $enabled]
                );
            }
        }

        return back()->with('success', 'Notification preferences updated');
    }
}
