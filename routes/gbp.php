<?php

use App\Http\Controllers\GBP\GBPLocationController;
use App\Http\Controllers\GBP\GBPPostController;
use App\Http\Controllers\GBP\GBPReviewController;
use App\Http\Controllers\GBP\GBPInsightController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('google-business')->name('gbp.')->group(function () {
        // Locations
        Route::get('/locations', [GBPLocationController::class, 'index'])->name('locations.index');
        Route::post('/locations/sync', [GBPLocationController::class, 'sync'])->name('locations.sync');
        Route::get('/locations/{location}', [GBPLocationController::class, 'show'])->name('locations.show');

        // Posts
        Route::get('/locations/{location}/posts', [GBPPostController::class, 'index'])->name('posts.index');
        Route::post('/locations/{location}/posts', [GBPPostController::class, 'store'])->name('posts.store');
        Route::delete('/posts/{post}', [GBPPostController::class, 'destroy'])->name('posts.destroy');

        // Reviews
        Route::get('/locations/{location}/reviews', [GBPReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/reply', [GBPReviewController::class, 'reply'])->name('reviews.reply');
        Route::post('/reviews/sync', [GBPReviewController::class, 'sync'])->name('reviews.sync');

        // Insights
        Route::get('/locations/{location}/insights', [GBPInsightController::class, 'index'])->name('insights.index');
        Route::post('/insights/sync', [GBPInsightController::class, 'sync'])->name('insights.sync');
    });
});
