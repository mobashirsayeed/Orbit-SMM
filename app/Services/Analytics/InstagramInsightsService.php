<?php

namespace App\Services\Analytics;

use App\Models\SocialAccount;
use App\Models\AnalyticsMetric;
use App\Models\AnalyticsSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramInsightsService
{
    public function syncInsights(SocialAccount $account): bool
    {
        try {
            $igAccountId = $account->meta['ig_account_id'] ?? $account->platform_account_id;
            $token = $account->token;

            // Get Instagram Business Account insights
            $response = Http::get("https://graph.facebook.com/v18.0/{$igAccountId}/insights", [
                'metric' => 'follower_count,impressions,reach,profile_views,website_clicks',
                'period' => 'day',
                'access_token' => $token,
            ]);

            if (!$response->successful()) {
                Log::error('Instagram Insights sync failed', [
                    'account_id' => $account->id,
                    'error' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json()['data'] ?? [];

            $metrics = [];
            foreach ($data as $insight) {
                $metricType = $this->mapInstagramMetric($insight['name']);
                $latestValue = end($insight['values'])['value'] ?? 0;
                $metrics[$metricType] = $latestValue;

                // Store individual metric
                AnalyticsMetric::updateOrCreate(
                    [
                        'tenant_id' => $account->tenant_id,
                        'social_account_id' => $account->id,
                        'metric_type' => $metricType,
                        'metric_date' => now()->format('Y-m-d'),
                    ],
                    [
                        'value' => (int) $latestValue,
                        'meta' => $insight,
                    ]
                );
            }

            // Create snapshot
            AnalyticsSnapshot::updateOrCreate(
                [
                    'tenant_id' => $account->tenant_id,
                    'platform' => 'instagram',
                    'snapshot_date' => now()->format('Y-m-d'),
                ],
                ['metrics' => $metrics]
            );

            $account->update(['last_analytics_sync' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('Instagram Insights sync exception', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function mapInstagramMetric(string $name): string
    {
        return match ($name) {
            'follower_count' => 'followers',
            'impressions' => 'impressions',
            'reach' => 'reach',
            'profile_views' => 'profile_views',
            'website_clicks' => 'website_clicks',
            default => $name,
        };
    }
}
