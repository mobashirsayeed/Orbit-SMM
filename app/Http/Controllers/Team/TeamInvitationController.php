<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class TeamInvitationController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = app('tenant')->id;

        $invitations = TeamInvitation::where('tenant_id', $tenantId)
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Team/Invitations', [
            'invitations' => $invitations,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,editor,viewer',
        ]);

        $tenantId = app('tenant')->id;
        $user = $request->user();

        // Check if user already exists in tenant
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->tenants()->where('tenant_id', $tenantId)->exists()) {
            return back()->withErrors(['email' => 'User is already a member of this workspace']);
        }

        // Check if invitation already exists
        $existingInvitation = TeamInvitation::where('tenant_id', $tenantId)
            ->where('email', $request->email)
            ->where('accepted_at', null)
            ->first();

        if ($existingInvitation && !$existingInvitation->isExpired()) {
            return back()->withErrors(['email' => 'Invitation already sent to this email']);
        }

        if ($existingInvitation) {
            $existingInvitation->delete();
        }

        $invitation = TeamInvitation::create([
            'tenant_id' => $tenantId,
            'invited_by' => $user->id,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        Mail::to($request->email)->send(new TeamInvitationMail($invitation));

        return back()->with('success', 'Invitation sent to ' . $request->email);
    }

    public function destroy(TeamInvitation $invitation)
    {
        $this->authorize('delete', $invitation);

        $invitation->revoke();

        return back()->with('success', 'Invitation revoked');
    }
}
