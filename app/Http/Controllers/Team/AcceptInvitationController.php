<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AcceptInvitationController extends Controller
{
    public function show($token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->withErrors(['invitation' => 'This invitation has expired']);
        }

        if ($invitation->accepted_at) {
            return redirect()->route('login')
                ->withErrors(['invitation' => 'This invitation has already been accepted']);
        }

        return Inertia::render('Team/AcceptInvitation', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(Request $request, $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            return back()->withErrors(['invitation' => 'This invitation has expired']);
        }

        if ($invitation->accepted_at) {
            return back()->withErrors(['invitation' => 'This invitation has already been accepted']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if user exists
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            event(new Registered($user));
        }

        $invitation->accept($user);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Invitation accepted! Welcome to ' . $invitation->tenant->name);
    }
}
