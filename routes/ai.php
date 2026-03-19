<?php

use App\Http\Controllers\AI\ContentGenerationController;
use App\Http\Controllers\AI\BrandVoiceController;
use App\Http\Controllers\AI\TemplateController;
use App\Http\Controllers\AI\HashtagController;
use App\Http\Controllers\AI\ImageGenerationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('ai')->name('ai.')->group(function () {
        // Content Generation
        Route::post('/generate', [ContentGenerationController::class, 'generate'])->name('generate');
        Route::post('/generate/stream', [ContentGenerationController::class, 'stream'])->name('generate.stream');
        Route::get('/history', [ContentGenerationController::class, 'history'])->name('history');

        // Brand Voice
        Route::get('/brand-voices', [BrandVoiceController::class, 'index'])->name('brand-voices.index');
        Route::post('/brand-voices', [BrandVoiceController::class, 'store'])->name('brand-voices.store');
        Route::put('/brand-voices/{voice}', [BrandVoiceController::class, 'update'])->name('brand-voices.update');
        Route::delete('/brand-voices/{voice}', [BrandVoiceController::class, 'destroy'])->name('brand-voices.destroy');

        // Templates
        Route::get('/templates', [TemplateController::class, 'index'])->name('ai.templates.index');
        Route::post('/templates', [TemplateController::class, 'store'])->name('ai.templates.store');
        Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('ai.templates.update');
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('ai.templates.destroy');

        // Hashtags
        Route::post('/hashtags/suggest', [HashtagController::class, 'suggest'])->name('hashtags.suggest');
        Route::get('/hashtags/sets', [HashtagController::class, 'sets'])->name('hashtags.sets');
        Route::post('/hashtags/sets', [HashtagController::class, 'saveSet'])->name('hashtags.save-set');

        // Image Generation
        Route::post('/images/generate', [ImageGenerationController::class, 'generate'])->name('images.generate');
        Route::post('/images/variation', [ImageGenerationController::class, 'variation'])->name('images.variation');
        Route::post('/images/edit', [ImageGenerationController::class, 'edit'])->name('images.edit');

        // Translation
        Route::post('/translate', [ContentGenerationController::class, 'translate'])->name('translate');
        
        // Grammar Check
        Route::post('/grammar/check', [ContentGenerationController::class, 'grammarCheck'])->name('grammar.check');
        Route::post('/grammar/improve', [ContentGenerationController::class, 'improve'])->name('grammar.improve');
    });
});
