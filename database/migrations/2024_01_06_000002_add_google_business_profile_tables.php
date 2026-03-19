<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gbp_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('location_id')->unique(); // Google location ID
            $table->string('place_id')->nullable();
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('categories')->nullable();
            $table->json('hours')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'location_id']);
        });

        Schema::create('gbp_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('gbp_locations')->cascadeOnDelete();
            $table->string('post_id')->nullable(); // Google post ID
            $table->enum('post_type', ['standard', 'offer', 'event', 'product']);
            $table->string('headline')->nullable();
            $table->text('content');
            $table->string('cta_type')->nullable(); // BOOK, ORDER, SIGN_UP, etc.
            $table->string('cta_url')->nullable();
            $table->json('media_urls')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->json('metrics')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('gbp_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('gbp_locations')->cascadeOnDelete();
            $table->string('review_id')->unique();
            $table->string('reviewer_name');
            $table->string('reviewer_avatar')->nullable();
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamp('review_date');
            $table->text('reply')->nullable();
            $table->timestamp('reply_date')->nullable();
            $table->integer('sentiment_score')->nullable(); // -100 to 100
            $table->boolean('is_responded')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'rating']);
            $table->index(['tenant_id', 'is_responded']);
        });

        Schema::create('gbp_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('gbp_locations')->cascadeOnDelete();
            $table->date('insight_date');
            $table->integer('search_views')->default(0);
            $table->integer('map_views')->default(0);
            $table->integer('website_clicks')->default(0);
            $table->integer('direction_requests')->default(0);
            $table->integer('phone_calls')->default(0);
            $table->integer('photo_views')->default(0);
            $table->json('search_queries')->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'insight_date']);
        });

        Schema::create('gbp_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('gbp_locations')->cascadeOnDelete();
            $table->string('product_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->string('image_url')->nullable();
            $table->string('product_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gbp_products');
        Schema::dropIfExists('gbp_insights');
        Schema::dropIfExists('gbp_reviews');
        Schema::dropIfExists('gbp_posts');
        Schema::dropIfExists('gbp_locations');
    }
};
