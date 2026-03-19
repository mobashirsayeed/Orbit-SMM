<?php

use App\Http\Controllers\CRM\PipelineController;
use App\Http\Controllers\CRM\DealController;
use App\Http\Controllers\CRM\ActivityController;
use App\Http\Controllers\CRM\AutomationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('crm')->name('crm.')->group(function () {
        // Pipelines
        Route::get('/pipelines', [PipelineController::class, 'index'])->name('pipelines.index');
        Route::post('/pipelines', [PipelineController::class, 'store'])->name('pipelines.store');
        Route::put('/pipelines/{pipeline}', [PipelineController::class, 'update'])->name('pipelines.update');
        Route::delete('/pipelines/{pipeline}', [PipelineController::class, 'destroy'])->name('pipelines.destroy');

        // Deals
        Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
        Route::get('/deals/create', [DealController::class, 'create'])->name('deals.create');
        Route::post('/deals', [DealController::class, 'store'])->name('deals.store');
        Route::get('/deals/{deal}', [DealController::class, 'show'])->name('deals.show');
        Route::put('/deals/{deal}', [DealController::class, 'update'])->name('deals.update');
        Route::delete('/deals/{deal}', [DealController::class, 'destroy'])->name('deals.destroy');
        Route::post('/deals/{deal}/win', [DealController::class, 'win'])->name('deals.win');
        Route::post('/deals/{deal}/lose', [DealController::class, 'lose'])->name('deals.lose');
        Route::post('/deals/{deal}/move', [DealController::class, 'moveStage'])->name('deals.move');

        // Activities
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::post('/deals/{deal}/activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete'])->name('activities.complete');

        // Automation
        Route::get('/automation', [AutomationController::class, 'index'])->name('automation.index');
        Route::post('/automation', [AutomationController::class, 'store'])->name('automation.store');
        Route::put('/automation/{rule}', [AutomationController::class, 'update'])->name('automation.update');
        Route::delete('/automation/{rule}', [AutomationController::class, 'destroy'])->name('automation.destroy');
    });
});
