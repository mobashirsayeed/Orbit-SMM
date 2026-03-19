<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_voices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('tone')->default('professional'); // professional, casual, friendly, authoritative
            $table->text('instructions')->nullable();
            $table->json('examples')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('content_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('prompt_template');
            $table->string('category')->nullable(); // caption, blog, email, ad
            $table->json('variables')->nullable();
            $table->boolean('is_public')->default(false);
            $table->integer('uses_count')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'category']);
        });

        Schema::create('hashtag_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('hashtags');
            $table->string('category')->nullable();
            $table->integer('uses_count')->default(0);
            $table->timestamps();
        });

        Schema::table('ai_content_generations', function (Blueprint $table) {
            $table->foreignId('brand_voice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('content_templates')->nullOnDelete();
            $table->integer('credits_used')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('ai_content_generations', function (Blueprint $table) {
            $table->dropColumn(['brand_voice_id', 'template_id', 'credits_used']);
        });

        Schema::dropIfExists('hashtag_sets');
        Schema::dropIfExists('content_templates');
        Schema::dropIfExists('brand_voices');
    }
};
