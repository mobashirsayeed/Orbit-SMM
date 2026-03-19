<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_starred')->default(false)->after('status');
            $table->boolean('is_internal')->default(false)->after('is_starred');
            $table->foreignId('parent_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->timestamp('read_at')->nullable()->after('received_at');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->integer('unread_count')->default(0)->after('status');
            $table->timestamp('last_message_at')->nullable()->after('meta');
            $table->boolean('is_starred')->default(false)->after('last_message_at');
        });

        Schema::create('message_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('reply_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('content');
            $table->string('shortcut')->nullable();
            $table->json('platforms')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'is_public']);
        });

        Schema::create('contact_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // call, email, meeting, note
            $table->text('description')->nullable();
            $table->timestamp('occurred_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_interactions');
        Schema::dropIfExists('reply_templates');
        Schema::dropIfExists('message_notes');

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['unread_count', 'last_message_at', 'is_starred']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_starred', 'is_internal', 'parent_id', 'read_at']);
        });
    }
};
