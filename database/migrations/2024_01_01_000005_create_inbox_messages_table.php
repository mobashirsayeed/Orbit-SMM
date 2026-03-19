<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('channel_type');
            $table->string('external_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_avatar')->nullable();
            $table->string('sender_external_id')->nullable();
            $table->text('body');
            $table->foreignId('parent_id')->nullable()->constrained('inbox_messages')->nullOnDelete();
            $table->enum('status', ['unread', 'read', 'replied', 'archived'])->default('unread');
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contact_id')->nullable();
            $table->json('attachments')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['workspace_id', 'platform', 'external_id']);
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_messages');
    }
};
