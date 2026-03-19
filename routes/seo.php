<?php

use App\Http\Controllers\SEO\SEOController;
use App\Http\Controllers\SEO\KeywordController;
use App\Http\Controllers\SEO\SchemaController;
use App\Http\Controllers\SEO\SitemapController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('seo')->name('seo.')->group(function () {
        // Site Audit
        Route::get('/', [SEOController::class, 'index'])->name('seo.dashboard');
        Route::post('/monitors', [SEOController::class, 'store'])->name('monitors.store');
        Route::post('/monitors/{monitor}/crawl', [SEOController::class, 'crawl'])->name('monitors.crawl');
        Route::get('/monitors/{monitor}/audit', [SEOController::class, 'audit'])->name('monitors.audit');

        // Keyword Tracking
        Route::get('/keywords', [KeywordController::class, 'index'])->name('keywords.index');
        Route::post('/keywords', [KeywordController::class, 'store'])->name('keywords.store');
        Route::get('/keywords/{keyword}/history', [KeywordController::class, 'history'])->name('keywords.history');

        // Schema Markup
        Route::get('/schema', [SchemaController::class, 'index'])->name('schema.index');
        Route::post('/schema', [SchemaController::class, 'store'])->name('schema.store');
        Route::get('/schema/{type}/generate', [SchemaController::class, 'generate'])->name('schema.generate');

        // Sitemap
        Route::get('/sitemap', [SitemapController::class, 'index'])->name('sitemap.index');
        Route::post('/sitemap/generate', [SitemapController::class, 'generate'])->name('sitemap.generate');
    });
});
