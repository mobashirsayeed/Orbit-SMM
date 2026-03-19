<?php

use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    // Email Verification
    Route::get('email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');

    // Two-Factor Authentication
    Route::get('two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
    Route::post('two-factor/enable', [TwoFactorAuthenticationController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('two-factor/disable', [TwoFactorAuthenticationController::class, 'disable'])
        ->name('two-factor.disable');
    Route::post('two-factor/regenerate-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.regenerate');

    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('two-factor.verify');
    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store']);
});
