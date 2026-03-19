<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('platform_account_id');
            $table->string('account_name')->nullable();
            $table->string('account_avatar')->nullable();
            $table->text('token');
            $table->text('token_secret')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(
                ['workspace_id', 'platform', 'platform_account_id'],
                'social_accounts_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
