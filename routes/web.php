<?php

use App\Http\Controllers\Auth\{LoginController, PasswordResetController, RegisterController};
use App\Http\Controllers\Social\{OAuthController, PostController, MediaController, CalendarController, KeywordController, SchedulerController};
use App\Http\Controllers\Inbox\{InboxController, ReplyController, AssignController, CannedResponseController};
use App\Http\Controllers\SEO\{CrawlController, RankController};
use App\Http\Controllers\GBP\{LocationController as GbpLC, ReviewController, ReviewRequestController};
use App\Http\Controllers\Ads\CampaignController;
use App\Http\Controllers\AI\{ContentGenerationController, ImageGenerationController};
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\CRM\ContactController;
use App\Http\Controllers\Agency\{AgencyController, ApprovalController};
use App\Http\Controllers\Billing\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    
    Route::post('/workspace/switch/{workspace}', fn(\App\Models\Workspace $w, \Illuminate\Http\Request $r) =>
        tap(back(), fn() => $r->user()->switchWorkspace($w))
    )->name('workspace.switch');

    // Social Media Routes
    Route::get('/social/connect/{platform}', [OAuthController::class, 'redirect'])->name('social.connect');
    Route::get('/social/callback/{platform}', [OAuthController::class, 'callback'])->name('social.callback');
    Route::delete('/social/disconnect/{platform}/{account}', [OAuthController::class, 'disconnect']);
    Route::get('/settings/accounts', fn() => Inertia::render('Settings/ConnectedAccounts', [
        'accounts' => \App\Models\SocialAccount::all()
    ]))->name('settings.accounts');
    Route::get('/social/posts', [PostController::class, 'index'])->name('social.posts');
    Route::get('/social/composer', [PostController::class, 'create'])->name('social.composer');
    Route::post('/social/posts', [PostController::class, 'store']);
    Route::patch('/social/posts/{post}/reschedule', [PostController::class, 'reschedule']);
    Route::post('/social/media', [MediaController::class, 'store']);
    Route::get('/social/calendar', [CalendarController::class, 'index'])->name('social.calendar');
    Route::get('/social/monitoring', [KeywordController::class, 'index'])->name('social.monitoring');
    Route::post('/social/keywords', [KeywordController::class, 'store']);
    Route::delete('/social/keywords/{keyword}', [KeywordController::class, 'destroy']);

    // Inbox Routes
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::get('/inbox/{message}', [InboxController::class, 'show']);
    Route::post('/inbox/{message}/reply', [ReplyController::class, 'store']);
    Route::post('/inbox/{message}/assign', [AssignController::class, 'store']);
    Route::resource('canned-responses', CannedResponseController::class)->only(['index', 'store', 'update', 'destroy']);

    // SEO Routes
    Route::get('/seo/audit', [CrawlController::class, 'index'])->name('seo.audit');
    Route::post('/seo/crawl', [CrawlController::class, 'start']);
    Route::get('/seo/rankings', [RankController::class, 'index'])->name('seo.rankings');
    Route::post('/seo/rankings', [RankController::class, 'store']);
    Route::delete('/seo/rankings/{keyword}', [RankController::class, 'destroy']);

    // Google Business Profile Routes
    Route::get('/gbp', [GbpLC::class, 'index'])->name('gbp');
    Route::get('/gbp/reviews', [ReviewController::class, 'index'])->name('gbp.reviews');
    Route::post('/gbp/reviews/{review}/reply', [ReviewController::class, 'reply']);

    // Ads Routes
    Route::get('/ads', [CampaignController::class, 'index'])->name('ads.index');
    Route::get('/ads/create', [CampaignController::class, 'create']);
    Route::post('/ads', [CampaignController::class, 'store']);

    // AI Routes
    Route::get('/ai/content', [ContentController::class, 'index'])->name('ai.content');
    Route::post('/ai/generate-caption', [ContentController::class, 'generateCaption']);
    Route::post('/ai/generate-blog', [ContentController::class, 'generateBlog']);
    Route::post('/ai/repurpose', [ContentController::class, 'repurpose']);
    Route::get('/ai/images', [ImageController::class, 'index'])->name('ai.images');

    // Analytics Routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // CRM Routes
    Route::get('/crm/contacts', [ContactController::class, 'index'])->name('crm.contacts');
    Route::get('/crm/contacts/{contact}', [ContactController::class, 'show']);
    Route::put('/crm/contacts/{contact}', [ContactController::class, 'update']);

    // Agency Routes
    Route::get('/agency/clients', [AgencyController::class, 'clients'])->name('agency.clients');
    Route::get('/agency/settings', [AgencyController::class, 'settings'])->name('agency.settings');
    Route::put('/agency/settings', [AgencyController::class, 'updateSettings']);
    Route::get('/agency/approvals', [ApprovalController::class, 'index'])->name('agency.approvals');
    Route::post('/agency/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/agency/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

    // Billing Routes
    Route::get('/billing/plans', [SubscriptionController::class, 'plans'])->name('billing.plans');
    Route::post('/billing/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/billing/usage', [SubscriptionController::class, 'usage'])->name('billing.usage');
    Route::get('/billing/portal', [SubscriptionController::class, 'portal'])->name('billing.portal');
});

// Cron Job Routes (Protected by Secret Header)
Route::post('/cron/process-posts', [SchedulerController::class, 'processScheduledPosts']);
Route::post('/cron/retry-failed', [SchedulerController::class, 'retryFailedJobs']);
