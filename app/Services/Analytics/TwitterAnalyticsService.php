<?php

namespace App\Services\Analytics;

use App\Models\SocialAccount;
use App\Models\AnalyticsMetric;
use App\Models\AnalyticsSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterAnalyticsService
{
    public function syncAnalytics(SocialAccount $account): bool
    {
        try {
            $userId = $account->platform_account_id;
            $token = $account->token;

            // Get user metrics
            $response = Http::withToken($token)
                ->get("https://api.twitter.com/2/users/{$userId}", [
                    'user.fields' => 'public_metrics,created_at',
                ]);

            if (!$response->successful()) {
                Log::error('Twitter Analytics sync failed', [
                    'account_id' => $account->id,
                    'error' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json()['data'] ?? [];
            $metrics = $data['public_metrics'] ?? [];

            // Store follower count
            AnalyticsMetric::updateOrCreate(
                [
                    'tenant_id' => $account->tenant_id,
                    'social_account_id' => $account->id,
                    'metric_type' => 'followers',
                    'metric_date' => now()->format('Y-m-d'),
                ],
                [
                    'value' => $metrics['followers_count'] ?? 0,
                    'meta' => $metrics,
                ]
            );

            // Create snapshot
            AnalyticsSnapshot::updateOrCreate(
                [
                    'tenant_id' => $account->tenant_id,
                    'platform' => 'twitter',
                    'snapshot_date' => now()->format('Y-m-d'),
                ],
                [
                    'metrics' => [
                        'followers' => $metrics['followers_count'] ?? 0,
                        'following' => $metrics['following_count'] ?? 0,
                        'tweets' => $metrics['tweet_count'] ?? 0,
                        'listed' => $metrics['listed_count'] ?? 0,
                    ],
                ]
            );

            $account->update(['last_analytics_sync' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('Twitter Analytics sync exception', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
