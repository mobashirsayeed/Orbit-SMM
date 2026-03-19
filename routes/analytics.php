<?php

use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Analytics\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('analytics')->name('analytics.')->group(function () {
        // Dashboard
        Route::get('/', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        Route::get('/sync', [AnalyticsController::class, 'sync'])->name('analytics.sync');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export');

        // Platform-specific
        Route::get('/platform/{platform}', [AnalyticsController::class, 'platform'])->name('platform');

        // Reports
        Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports');
        Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    });
});
