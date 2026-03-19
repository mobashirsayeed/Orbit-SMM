<?php

use App\Http\Controllers\Team\TeamInvitationController;
use App\Http\Controllers\Team\AcceptInvitationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('invitations', [TeamInvitationController::class, 'index'])
            ->name('invitations.index');
        Route::post('invitations', [TeamInvitationController::class, 'store'])
            ->name('invitations.store');
        Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
            ->name('invitations.destroy');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('team/invite/{token}', [AcceptInvitationController::class, 'show'])
        ->name('team.invitations.show');
    Route::post('team/invite/{token}', [AcceptInvitationController::class, 'accept'])
        ->name('team.invitations.accept');
});
