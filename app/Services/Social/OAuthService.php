<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Exception;

class OAuthService
{
    public function getFacebookAuthUrl(): string
    {
        $params = [
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect'),
            'scope' => 'pages_manage_posts,pages_read_engagement,instagram_basic,instagram_content_publish',
            'response_type' => 'code',
        ];

        return 'https://www.facebook.com/' . config('services.facebook.graph_version') . '/dialog/oauth?' . http_build_query($params);
    }

    public function getTwitterAuthUrl(): string
    {
        $params = [
            'client_id' => config('services.twitter.client_id'),
            'redirect_uri' => config('services.twitter.redirect'),
            'scope' => 'tweet.read tweet.write users.read offline.access',
            'response_type' => 'code',
            'code_challenge' => $this->generateCodeChallenge(),
            'code_challenge_method' => 'S256',
            'state' => bin2hex(random_bytes(16)),
        ];

        session(['twitter_state' => $params['state']]);

        return 'https://twitter.com/i/oauth2/authorize?' . http_build_query($params);
    }

    public function getLinkedInAuthUrl(): string
    {
        $params = [
            'client_id' => config('services.linkedin.client_id'),
            'redirect_uri' => config('services.linkedin.redirect'),
            'scope' => implode(' ', config('services.linkedin.scopes')),
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16)),
        ];

        session(['linkedin_state' => $params['state']]);

        return 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
    }

    public function exchangeFacebookCode(string $code): array
    {
        $response = Http::post('https://graph.facebook.com/' . config('services.facebook.graph_version') . '/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => config('services.facebook.redirect'),
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new Exception('Facebook OAuth failed: ' . $response->body());
        }

        $data = $response->json();

        // Get long-lived token
        $longLived = Http::get('https://graph.facebook.com/' . config('services.facebook.graph_version') . '/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $data['access_token'],
        ])->json();

        // Get user pages
        $pages = Http::get('https://graph.facebook.com/' . config('services.facebook.graph_version') . '/me/accounts', [
            'access_token' => $longLived['access_token'],
        ])->json();

        return [
            'access_token' => $longLived['access_token'],
            'expires_at' => now()->addDays(60),
            'pages' => $pages['data'] ?? [],
        ];
    }

    public function exchangeTwitterCode(string $code, string $codeVerifier): array
    {
        $response = Http::withBasicAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret')
        )->post('https://api.twitter.com/2/oauth2/token', [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.twitter.redirect'),
            'code_verifier' => $codeVerifier,
        ]);

        if (!$response->successful()) {
            throw new Exception('Twitter OAuth failed: ' . $response->body());
        }

        $data = $response->json();

        // Get user info
        $user = Http::withToken($data['access_token'])
            ->get('https://api.twitter.com/2/users/me')
            ->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 7200),
            'user' => $data['data'] ?? [],
        ];
    }

    public function exchangeLinkedInCode(string $code): array
    {
        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.linkedin.redirect'),
            'client_id' => config('services.linkedin.client_id'),
            'client_secret' => config('services.linkedin.client_secret'),
        ]);

        if (!$response->successful()) {
            throw new Exception('LinkedIn OAuth failed: ' . $response->body());
        }

        $data = $response->json();

        // Get user info
        $user = Http::withToken($data['access_token'])
            ->get('https://api.linkedin.com/v2/me')
            ->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'user' => $user,
        ];
    }

    public function storeAccount(array $data, string $platform, int $tenantId, int $userId): SocialAccount
    {
        return SocialAccount::create([
            'tenant_id' => $tenantId,
            'platform' => $platform,
            'platform_account_id' => $data['id'] ?? $data['user']['id'] ?? null,
            'account_name' => $data['name'] ?? $data['user']['name'] ?? null,
            'account_avatar' => $data['avatar'] ?? $data['user']['profile_image_url'] ?? null,
            'token' => Crypt::encryptString($data['access_token']),
            'refresh_token' => $data['refresh_token'] ? Crypt::encryptString($data['refresh_token']) : null,
            'token_expires_at' => $data['expires_at'] ?? null,
            'meta' => $data,
        ]);
    }

    private function generateCodeChallenge(): string
    {
        $codeVerifier = bin2hex(random_bytes(32));
        session(['twitter_code_verifier' => $codeVerifier]);
        
        $hash = hash('sha256', $codeVerifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}
