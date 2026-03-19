<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorChallengeController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = User::find(session()->get('2fa_user_id'));

        if (!$user || !$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        Auth::login($user);
        session()->put('2fa_verified', true);
        session()->forget('2fa_user_id');

        return redirect()->intended('/dashboard');
    }
}
