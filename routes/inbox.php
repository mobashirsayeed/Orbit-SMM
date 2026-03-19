<?php

use App\Http\Controllers\Inbox\InboxController;
use App\Http\Controllers\Inbox\MessageController;
use App\Http\Controllers\Inbox\ConversationController;
use App\Http\Controllers\Inbox\ReplyTemplateController;
use App\Http\Controllers\Inbox\MessageNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'subscribed'])->group(function () {
    Route::prefix('inbox')->name('inbox.')->group(function () {
        // Main Inbox
        Route::get('/', [InboxController::class, 'index'])->name('index');
        Route::get('/{conversation}', [InboxController::class, 'show'])->name('show');
        Route::get('/api/poll', [InboxController::class, 'poll'])->name('poll');
        Route::get('/api/unread-count', [InboxController::class, 'unreadCount'])->name('unread-count');
        Route::post('/sync', [InboxController::class, 'sync'])->name('inbox.sync');

        // Messages
        Route::post('/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::put('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
        Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
        Route::post('/messages/{message}/star', [MessageController::class, 'toggleStar'])->name('messages.star');

        // Conversations
        Route::post('/conversations/{conversation}/assign', [ConversationController::class, 'assign'])->name('conversations.assign');
        Route::post('/conversations/{conversation}/close', [ConversationController::class, 'close'])->name('conversations.close');
        Route::post('/conversations/{conversation}/reopen', [ConversationController::class, 'reopen'])->name('conversations.reopen');
        Route::post('/conversations/{conversation}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
        Route::post('/conversations/{conversation}/star', [ConversationController::class, 'toggleStar'])->name('conversations.star');
        Route::post('/conversations/bulk', [ConversationController::class, 'bulkUpdate'])->name('conversations.bulk');

        // Reply Templates
        Route::get('/templates', [ReplyTemplateController::class, 'index'])->name('inbox.templates.index');
        Route::post('/templates', [ReplyTemplateController::class, 'store'])->name('inbox.templates.store');
        Route::put('/templates/{template}', [ReplyTemplateController::class, 'update'])->name('inbox.templates.update');
        Route::delete('/templates/{template}', [ReplyTemplateController::class, 'destroy'])->name('inbox.templates.destroy');

        // Message Notes
        Route::post('/conversations/{conversation}/notes', [MessageNoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [MessageNoteController::class, 'update'])->name('notes.update');
        Route::delete('/notes/{note}', [MessageNoteController::class, 'destroy'])->name('notes.destroy');
    });
});
