<?php

use App\Http\Controllers\Social\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('accounts', [SocialAuthController::class, 'index'])
            ->name('accounts.index');
        Route::get('connect/{platform}', [SocialAuthController::class, 'redirect'])
            ->name('connect');
        Route::get('callback/{platform}', [SocialAuthController::class, 'callback'])
            ->name('callback');
        Route::delete('accounts/{account}', [SocialAuthController::class, 'disconnect'])
            ->name('disconnect');
        Route::post('accounts/{account}/refresh', [SocialAuthController::class, 'refresh'])
            ->name('refresh');
    });
});
