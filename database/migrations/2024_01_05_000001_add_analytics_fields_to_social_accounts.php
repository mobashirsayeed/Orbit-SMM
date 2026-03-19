<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->json('analytics_metrics')->nullable()->after('meta');
            $table->timestamp('last_analytics_sync')->nullable()->after('analytics_metrics');
        });

        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained()->cascadeOnDelete();
            $table->string('metric_type'); // followers, engagement, reach, impressions
            $table->integer('value')->default(0);
            $table->integer('change')->default(0); // change from previous period
            $table->date('metric_date');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'social_account_id', 'metric_date']);
            $table->index(['tenant_id', 'metric_type', 'metric_date']);
        });

        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->json('metrics');
            $table->date('snapshot_date');
            $table->timestamps();

            $table->index(['tenant_id', 'platform', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
        Schema::dropIfExists('analytics_metrics');

        Schema::table('social_accounts', function (Blueprint $table) {
            $table->dropColumn(['analytics_metrics', 'last_analytics_sync']);
        });
    }
};
