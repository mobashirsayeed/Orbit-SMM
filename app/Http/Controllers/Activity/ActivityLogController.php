<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = app('tenant')->id;

        $query = ActivityLog::where('tenant_id', $tenantId)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('Activity/Index', [
            'logs' => $logs,
            'filters' => $request->only(['user_id', 'event', 'date_from', 'date_to']),
        ]);
    }

    public function show(ActivityLog $log)
    {
        $this->authorize('view', $log);

        return Inertia::render('Activity/Show', [
            'log' => $log->load('user'),
        ]);
    }
}
