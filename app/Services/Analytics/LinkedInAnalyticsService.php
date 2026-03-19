<?php

namespace App\Services\Analytics;

use App\Models\SocialAccount;
use App\Models\AnalyticsMetric;
use App\Models\AnalyticsSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInAnalyticsService
{
    public function syncAnalytics(SocialAccount $account): bool
    {
        try {
            $personId = $account->platform_account_id;
            $token = $account->token;

            // Get profile statistics
            $response = Http::withToken($token)
                ->get('https://api.linkedin.com/v2/me', [
                    'projection' => '(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
                ]);

            if (!$response->successful()) {
                Log::error('LinkedIn Analytics sync failed', [
                    'account_id' => $account->id,
                    'error' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();

            // Get follower count (requires additional API call)
            $followers = $this->getFollowerCount($token, $personId);

            // Create snapshot
            AnalyticsSnapshot::updateOrCreate(
                [
                    'tenant_id' => $account->tenant_id,
                    'platform' => 'linkedin',
                    'snapshot_date' => now()->format('Y-m-d'),
                ],
                [
                    'metrics' => [
                        'followers' => $followers,
                        'profile_views' => 0, // Requires premium API
                        'post_impressions' => 0,
                    ],
                ]
            );

            $account->update(['last_analytics_sync' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('LinkedIn Analytics sync exception', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function getFollowerCount(string $token, string $personId): int
    {
        try {
            $response = Http::withToken($token)
                ->get('https://api.linkedin.com/v2/socialActions', [
                    'q' => 'actors',
                    'actors' => "urn:li:person:{$personId}",
                ]);

            if ($response->successful()) {
                return $response->json()['elements'][0]['totalShareCount'] ?? 0;
            }
        } catch (\Exception $e) {
            Log::warning('LinkedIn follower count fetch failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return 0;
    }
}
