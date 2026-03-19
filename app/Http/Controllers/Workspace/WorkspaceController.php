<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $workspaces = $request->user()->tenants()
            ->with('subscription')
            ->orderBy('name')
            ->get();

        return Inertia::render('Workspace/Index', [
            'workspaces' => $workspaces,
            'currentWorkspace' => $request->user()->currentTenant(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Workspace/Create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Check plan limits
        $currentPlan = config('plans.' . $user->currentTenant()?->plan ?? 'starter');
        $workspaceCount = $user->tenants()->count();

        if ($workspaceCount >= $currentPlan['limits']['workspaces']) {
            return back()->withErrors(['workspace' => 'You have reached your workspace limit']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = Tenant::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'owner_id' => $user->id,
            'plan' => 'starter',
            'active' => true,
        ]);

        $user->tenants()->attach($workspace->id, ['role' => 'admin']);
        $user->update(['current_tenant_id' => $workspace->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Workspace created successfully');
    }

    public function switch(Request $request, Tenant $workspace)
    {
        $user = $request->user();

        if (!$user->tenants()->where('tenant_id', $workspace->id)->exists()) {
            return back()->withErrors(['workspace' => 'You do not have access to this workspace']);
        }

        $user->switchTenant($workspace);

        return redirect()->route('dashboard')
            ->with('success', 'Switched to ' . $workspace->name);
    }

    public function update(Request $request, Tenant $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'settings' => 'nullable|array',
        ]);

        $workspace->update($validated);

        return back()->with('success', 'Workspace updated successfully');
    }

    public function destroy(Request $request, Tenant $workspace)
    {
        $this->authorize('delete', $workspace);

        // Prevent deleting the last workspace
        if ($request->user()->tenants()->count() <= 1) {
            return back()->withErrors(['workspace' => 'Cannot delete your last workspace']);
        }

        $workspace->delete();

        return redirect()->route('workspace.index')
            ->with('success', 'Workspace deleted successfully');
    }
}
