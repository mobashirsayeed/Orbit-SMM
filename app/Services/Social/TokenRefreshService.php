<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Exception;

class TokenRefreshService
{
    public function refreshToken(SocialAccount $account): bool
    {
        if (!$account->isExpired() && $account->token_expires_at?->isFuture()) {
            return true; // Token still valid
        }

        try {
            $newTokenData = match ($account->platform) {
                'facebook' => $this->refreshFacebookToken($account),
                'twitter' => $this->refreshTwitterToken($account),
                'linkedin' => $this->refreshLinkedInToken($account),
                'instagram' => $this->refreshFacebookToken($account), // Same as Facebook
                default => throw new Exception('Unsupported platform'),
            };

            $account->update([
                'token' => Crypt::encryptString($newTokenData['access_token']),
                'refresh_token' => $newTokenData['refresh_token'] 
                    ? Crypt::encryptString($newTokenData['refresh_token']) 
                    : null,
                'token_expires_at' => $newTokenData['expires_at'],
            ]);

            return true;
        } catch (Exception $e) {
            \Log::error('Token refresh failed', [
                'account_id' => $account->id,
                'platform' => $account->platform,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function refreshAllExpiredTokens(): int
    {
        $expiredAccounts = SocialAccount::where('token_expires_at', '<=', now())
            ->orWhereNull('token_expires_at')
            ->get();

        $refreshed = 0;

        foreach ($expiredAccounts as $account) {
            if ($this->refreshToken($account)) {
                $refreshed++;
            }
        }

        return $refreshed;
    }

    private function refreshFacebookToken(SocialAccount $account): array
    {
        $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $account->token,
        ]);

        if (!$response->successful()) {
            throw new Exception('Facebook token refresh failed');
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => null,
            'expires_at' => now()->addDays(60),
        ];
    }

    private function refreshTwitterToken(SocialAccount $account): array
    {
        if (!$account->refresh_token) {
            throw new Exception('Twitter refresh token not available');
        }

        $response = Http::withBasicAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret')
        )->post('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);

        if (!$response->successful()) {
            throw new Exception('Twitter token refresh failed');
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 7200),
        ];
    }

    private function refreshLinkedInToken(SocialAccount $account): array
    {
        if (!$account->refresh_token) {
            throw new Exception('LinkedIn refresh token not available');
        }

        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
            'client_id' => config('services.linkedin.client_id'),
            'client_secret' => config('services.linkedin.client_secret'),
        ]);

        if (!$response->successful()) {
            throw new Exception('LinkedIn token refresh failed');
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ];
    }
}
