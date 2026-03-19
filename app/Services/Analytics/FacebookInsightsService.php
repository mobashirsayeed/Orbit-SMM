<?php

namespace App\Services\Analytics;

use App\Models\SocialAccount;
use App\Models\AnalyticsMetric;
use App\Models\AnalyticsSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookInsightsService
{
    public function syncInsights(SocialAccount $account): bool
    {
        try {
            $pageId = $account->platform_account_id;
            $token = $account->token;

            // Get page insights
            $response = Http::get("https://graph.facebook.com/v18.0/{$pageId}/insights", [
                'metric' => 'page_fan_adds,page_impressions,page_engaged_users,page_post_engagements,page_views',
                'period' => 'day',
                'since' => now()->subDays(30)->format('Y-m-d'),
                'until' => now()->format('Y-m-m'),
                'access_token' => $token,
            ]);

            if (!$response->successful()) {
                Log::error('Facebook Insights sync failed', [
                    'account_id' => $account->id,
                    'error' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json()['data'] ?? [];

            foreach ($data as $insight) {
                $this->storeFacebookMetric($account, $insight);
            }

            // Create snapshot
            $this->createFacebookSnapshot($account, $data);

            $account->update(['last_analytics_sync' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('Facebook Insights sync exception', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function storeFacebookMetric(SocialAccount $account, array $insight): void
    {
        $metricType = $this->mapFacebookMetric($insight['name']);

        foreach ($insight['values'] as $value) {
            if (!isset($value['value'])) {
                continue;
            }

            $date = isset($value['end_time']) 
                ? \Carbon\Carbon::parse($value['end_time'])->format('Y-m-d')
                : now()->format('Y-m-d');

            AnalyticsMetric::updateOrCreate(
                [
                    'tenant_id' => $account->tenant_id,
                    'social_account_id' => $account->id,
                    'metric_type' => $metricType,
                    'metric_date' => $date,
                ],
                [
                    'value' => (int) $value['value'],
                    'meta' => $insight,
                ]
            );
        }
    }

    private function createFacebookSnapshot(SocialAccount $account, array $data): void
    {
        $metrics = [];

        foreach ($data as $insight) {
            $metricType = $this->mapFacebookMetric($insight['name']);
            $latestValue = end($insight['values'])['value'] ?? 0;
            $metrics[$metricType] = $latestValue;
        }

        AnalyticsSnapshot::updateOrCreate(
            [
                'tenant_id' => $account->tenant_id,
                'platform' => 'facebook',
                'snapshot_date' => now()->format('Y-m-d'),
            ],
            ['metrics' => $metrics]
        );
    }

    private function mapFacebookMetric(string $name): string
    {
        return match ($name) {
            'page_fan_adds' => 'followers',
            'page_impressions' => 'impressions',
            'page_engaged_users' => 'engagement',
            'page_post_engagements' => 'post_engagement',
            'page_views' => 'page_views',
            default => $name,
        };
    }
}
