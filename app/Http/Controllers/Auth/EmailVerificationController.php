<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    }

    public function verify(Request $request, $id, $hash)
    {
        if (!hash_equals((string) $id, (string) $request->user()->getKey())) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'Invalid verification link']);
        }

        if (!hash_equals((string) $hash, sha1($request->user()->getEmailForVerification()))) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'Invalid verification link']);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('success', 'Email already verified');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('dashboard')
            ->with('success', 'Email verified successfully');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('success', 'Email already verified');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent');
    }
}
