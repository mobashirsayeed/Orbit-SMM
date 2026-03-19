<?php

namespace App\Services\Analytics;

use App\Models\SocialAccount;
use App\Services\Analytics\FacebookInsightsService;
use App\Services\Analytics\TwitterAnalyticsService;
use App\Services\Analytics\LinkedInAnalyticsService;
use App\Services\Analytics\InstagramInsightsService;

class AnalyticsSyncService
{
    public function __construct(
        private readonly FacebookInsightsService $facebookService,
        private readonly TwitterAnalyticsService $twitterService,
        private readonly LinkedInAnalyticsService $linkedInService,
        private readonly InstagramInsightsService $instagramService,
    ) {}

    public function syncAll(int $tenantId): array
    {
        $accounts = SocialAccount::where('tenant_id', $tenantId)->get();
        $results = [];

        foreach ($accounts as $account) {
            $success = match ($account->platform) {
                'facebook' => $this->facebookService->syncInsights($account),
                'twitter' => $this->twitterService->syncAnalytics($account),
                'linkedin' => $this->linkedInService->syncAnalytics($account),
                'instagram' => $this->instagramService->syncInsights($account),
                default => false,
            };

            $results[$account->platform] = $success;
        }

        return $results;
    }

    public function syncPlatform(int $tenantId, string $platform): bool
    {
        $account = SocialAccount::where('tenant_id', $tenantId)
            ->where('platform', $platform)
            ->first();

        if (!$account) {
            return false;
        }

        return match ($platform) {
            'facebook' => $this->facebookService->syncInsights($account),
            'twitter' => $this->twitterService->syncAnalytics($account),
            'linkedin' => $this->linkedInService->syncAnalytics($account),
            'instagram' => $this->instagramService->syncInsights($account),
            default => false,
        };
    }

    public function getDashboardMetrics(int $tenantId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $metrics = [
            'total_followers' => 0,
            'total_engagement' => 0,
            'total_impressions' => 0,
            'total_reach' => 0,
            'follower_change' => 0,
            'engagement_rate' => 0,
            'top_platform' => null,
            'platforms' => [],
        ];

        $platforms = ['facebook', 'twitter', 'linkedin', 'instagram'];

        foreach ($platforms as $platform) {
            $platformMetrics = $this->getPlatformMetrics($tenantId, $platform, $startDate, $endDate);
            $metrics['platforms'][$platform] = $platformMetrics;
            $metrics['total_followers'] += $platformMetrics['followers'] ?? 0;
            $metrics['total_engagement'] += $platformMetrics['engagement'] ?? 0;
            $metrics['total_impressions'] += $platformMetrics['impressions'] ?? 0;
            $metrics['total_reach'] += $platformMetrics['reach'] ?? 0;
        }

        // Calculate engagement rate
        if ($metrics['total_impressions'] > 0) {
            $metrics['engagement_rate'] = round(
                ($metrics['total_engagement'] / $metrics['total_impressions']) * 100, 
                2
            );
        }

        // Find top platform
        $metrics['top_platform'] = collect($metrics['platforms'])
            ->sortByDesc('followers')
            ->keys()
            ->first();

        return $metrics;
    }

    private function getPlatformMetrics(int $tenantId, string $platform, $startDate, $endDate): array
    {
        $snapshot = AnalyticsSnapshot::where('tenant_id', $tenantId)
            ->where('platform', $platform)
            ->where('snapshot_date', '>=', $startDate)
            ->latest('snapshot_date')
            ->first();

        if (!$snapshot) {
            return ['followers' => 0, 'engagement' => 0, 'impressions' => 0, 'reach' => 0];
        }

        return [
            'followers' => $snapshot->metrics['followers'] ?? 0,
            'engagement' => $snapshot->metrics['engagement'] ?? 0,
            'impressions' => $snapshot->metrics['impressions'] ?? 0,
            'reach' => $snapshot->metrics['reach'] ?? 0,
        ];
    }
}
