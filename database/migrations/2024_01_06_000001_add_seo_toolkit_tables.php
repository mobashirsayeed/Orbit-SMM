<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_monitors', function (Blueprint $table) {
            $table->integer('seo_score')->default(0)->after('domain');
            $table->integer('performance_score')->default(0)->after('seo_score');
            $table->integer('accessibility_score')->default(0)->after('performance_score');
            $table->integer('best_practices_score')->default(0)->after('accessibility_score');
            $table->json('issues')->nullable()->after('results');
            $table->json('recommendations')->nullable()->after('issues');
        });

        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seo_monitor_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->integer('status_code')->nullable();
            $table->integer('load_time')->nullable(); // in ms
            $table->integer('page_size')->nullable(); // in KB
            $table->json('meta_tags')->nullable();
            $table->json('headings')->nullable();
            $table->json('links')->nullable();
            $table->json('images')->nullable();
            $table->json('issues')->nullable();
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'seo_monitor_id']);
        });

        Schema::create('keyword_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seo_monitor_id')->constrained()->cascadeOnDelete();
            $table->string('keyword');
            $table->string('search_engine')->default('google');
            $table->string('location')->nullable();
            $table->integer('position')->nullable();
            $table->integer('previous_position')->nullable();
            $table->string('url')->nullable();
            $table->date('tracked_date');
            $table->json('serp_features')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'keyword', 'tracked_date']);
            $table->index(['seo_monitor_id', 'tracked_date']);
        });

        Schema::create('competitor_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seo_monitor_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->string('name')->nullable();
            $table->json('keywords')->nullable();
            $table->integer('avg_position')->nullable();
            $table->integer('visibility_score')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'domain']);
        });

        Schema::create('schema_markups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // Organization, LocalBusiness, Article, Product, etc.
            $table->json('schema_data');
            $table->string('page_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('xml_sitemaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('sitemap_name');
            $table->string('file_path');
            $table->integer('url_count')->default(0);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xml_sitemaps');
        Schema::dropIfExists('schema_markups');
        Schema::dropIfExists('competitor_tracking');
        Schema::dropIfExists('keyword_rankings');
        Schema::dropIfExists('seo_audits');

        Schema::table('seo_monitors', function (Blueprint $table) {
            $table->dropColumn([
                'seo_score',
                'performance_score',
                'accessibility_score',
                'best_practices_score',
                'issues',
                'recommendations',
            ]);
        });
    }
};
