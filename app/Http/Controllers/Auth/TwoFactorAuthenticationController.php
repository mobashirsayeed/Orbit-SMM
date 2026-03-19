<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        
        return Inertia::render('Auth/TwoFactorShow', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'secret' => $user->hasTwoFactorEnabled() ? null : $user->generateTwoFactorSecret(),
            'qrCodeUrl' => $user->hasTwoFactorEnabled() ? null : $this->generateQRCodeUrl($user),
            'recoveryCodes' => $user->hasTwoFactorEnabled() ? $user->getTwoFactorRecoveryCodes() : [],
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $user->enableTwoFactorAuthentication();

        return back()->with('success', 'Two-factor authentication enabled successfully');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $user->disableTwoFactorAuthentication();

        return back()->with('success', 'Two-factor authentication disabled successfully');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($user->generateRecoveryCodes())),
        ])->save();

        return back()->with('success', 'Recovery codes regenerated');
    }

    private function generateQRCodeUrl($user): string
    {
        $secret = $user->generateTwoFactorSecret();
        $issuer = urlencode(config('app.name'));
        $label = urlencode($user->email);
        
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}";
    }
}
