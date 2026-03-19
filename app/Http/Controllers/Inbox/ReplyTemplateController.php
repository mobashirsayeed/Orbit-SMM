<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\ReplyTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReplyTemplateController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = app('tenant')->id;

        $query = ReplyTemplate::where('tenant_id', $tenantId)
            ->where(function ($q) use ($request) {
                $q->where('is_public', true)
                    ->orWhere('user_id', $request->user()->id);
            });

        if ($request->filled('platform')) {
            $query->forPlatform($request->platform);
        }

        $templates = $query->orderBy('name')->get();

        return Inertia::render('Inbox/Templates', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'shortcut' => 'nullable|string|max:50',
            'platforms' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        ReplyTemplate::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return back()->with('success', 'Template created');
    }

    public function update(Request $request, ReplyTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'shortcut' => 'nullable|string|max:50',
            'platforms' => 'nullable|array',
            'is_public' => 'sometimes|boolean',
        ]);

        $template->update($validated);

        return back()->with('success', 'Template updated');
    }

    public function destroy(ReplyTemplate $template)
    {
        $this->authorize('delete', $template);

        $template->delete();

        return back()->with('success', 'Template deleted');
    }
}
