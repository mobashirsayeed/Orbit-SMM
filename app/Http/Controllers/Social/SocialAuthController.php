<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Services\Social\OAuthService;
use App\Services\Social\TokenRefreshService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly OAuthService $oauthService,
        private readonly TokenRefreshService $refreshService
    ) {}

    public function redirect(Request $request, string $platform)
    {
        $url = match ($platform) {
            'facebook' => $this->oauthService->getFacebookAuthUrl(),
            'twitter' => $this->oauthService->getTwitterAuthUrl(),
            'linkedin' => $this->oauthService->getLinkedInAuthUrl(),
            'instagram' => $this->oauthService->getFacebookAuthUrl(), // Same as Facebook
            default => abort(404, 'Unsupported platform'),
        };

        return redirect()->away($url);
    }

    public function callback(Request $request, string $platform)
    {
        $user = Auth::user();
        $tenantId = app('tenant')->id;

        try {
            $code = $request->get('code');

            $oauthData = match ($platform) {
                'facebook' => $this->oauthService->exchangeFacebookCode($code),
                'twitter' => $this->oauthService->exchangeTwitterCode(
                    $code, 
                    session('twitter_code_verifier')
                ),
                'linkedin' => $this->oauthService->exchangeLinkedInCode($code),
                'instagram' => $this->handleInstagramCallback($code),
                default => abort(404, 'Unsupported platform'),
            };

            // Check if account already exists
            $existingAccount = $this->findExistingAccount($platform, $oauthData, $tenantId);

            if ($existingAccount) {
                $this->updateExistingAccount($existingAccount, $oauthData);
                $message = 'Social account reconnected successfully';
            } else {
                // Check plan limits
                $this->checkAccountLimits($user, $platform);
                
                $this->oauthService->storeAccount($oauthData, $platform, $tenantId, $user->id);
                $message = 'Social account connected successfully';
            }

            return redirect()->route('social.accounts.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Social auth callback failed', [
                'platform' => $platform,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('social.accounts.index')
                ->with('error', 'Failed to connect account: ' . $e->getMessage());
        }
    }

    public function disconnect(Request $request, int $accountId)
    {
        $account = $request->user()
            ->currentTenant()
            ->socialAccounts()
            ->findOrFail($accountId);

        // Revoke token if possible
        $this->revokeToken($account);

        $account->delete();

        return back()->with('success', 'Social account disconnected');
    }

    public function refresh(Request $request, int $accountId)
    {
        $account = $request->user()
            ->currentTenant()
            ->socialAccounts()
            ->findOrFail($accountId);

        try {
            $this->refreshService->refreshToken($account);
            return back()->with('success', 'Token refreshed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to refresh token: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $accounts = $request->user()
            ->currentTenant()
            ->socialAccounts()
            ->orderBy('platform')
            ->get();

        return Inertia::render('Social/Accounts', [
            'accounts' => $accounts,
            'platforms' => [
                'facebook' => ['name' => 'Facebook', 'icon' => 'facebook', 'color' => '#1877F2'],
                'twitter' => ['name' => 'Twitter/X', 'icon' => 'twitter', 'color' => '#000000'],
                'linkedin' => ['name' => 'LinkedIn', 'icon' => 'linkedin', 'color' => '#0A66C2'],
                'instagram' => ['name' => 'Instagram', 'icon' => 'instagram', 'color' => '#E4405F'],
            ],
        ]);
    }

    private function handleInstagramCallback(string $code): array
    {
        // Instagram uses Facebook OAuth
        $fbData = $this->oauthService->exchangeFacebookCode($code);

        // Get Instagram Business Account
        $response = \Illuminate\Support\Facades\Http::get(
            'https://graph.facebook.com/v18.0/me/instagram_business_accounts',
            ['access_token' => $fbData['access_token']]
        );

        $igAccount = $response->json()['data'][0] ?? null;

        if (!$igAccount) {
            throw new \Exception('No Instagram Business Account found');
        }

        return array_merge($fbData, [
            'id' => $igAccount['id'],
            'name' => $igAccount['name'],
            'username' => $igAccount['username'],
            'ig_account_id' => $igAccount['id'],
        ]);
    }

    private function findExistingAccount(string $platform, array $oauthData, int $tenantId): ?\App\Models\SocialAccount
    {
        $accountId = $oauthData['id'] ?? $oauthData['user']['id'] ?? null;

        return SocialAccount::where('tenant_id', $tenantId)
            ->where('platform', $platform)
            ->where('platform_account_id', $accountId)
            ->first();
    }

    private function updateExistingAccount($account, array $oauthData): void
    {
        $account->update([
            'token' => \Illuminate\Support\Facades\Crypt::encryptString($oauthData['access_token']),
            'refresh_token' => $oauthData['refresh_token'] 
                ? \Illuminate\Support\Facades\Crypt::encryptString($oauthData['refresh_token']) 
                : null,
            'token_expires_at' => $oauthData['expires_at'] ?? null,
            'meta' => array_merge($account->meta ?? [], $oauthData),
        ]);
    }

    private function checkAccountLimits($user, string $platform): void
    {
        $currentPlan = config('plans.' . $user->currentTenant()->plan ?? 'starter');
        $accountCount = $user->currentTenant()->socialAccounts()->count();

        if ($accountCount >= $currentPlan['limits']['social_accounts']) {
            throw new \Exception('You have reached your social account limit');
        }
    }

    private function revokeToken($account): void
    {
        try {
            match ($account->platform) {
                'facebook' => \Illuminate\Support\Facades\Http::post(
                    'https://graph.facebook.com/v18.0/me/permissions',
                    ['access_token' => $account->token]
                ),
                'twitter' => \Illuminate\Support\Facades\Http::withToken($account->token)
                    ->post('https://api.twitter.com/2/oauth2/revoke'),
                'linkedin' => \Illuminate\Support\Facades\Http::post(
                    'https://www.linkedin.com/oauth/v2/revoke',
                    ['token' => $account->token]
                ),
            };
        } catch (\Exception $e) {
            \Log::warning('Failed to revoke social token', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
