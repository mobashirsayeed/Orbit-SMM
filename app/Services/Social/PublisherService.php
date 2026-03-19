<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class PublisherService
{
    public function publishToFacebook(Post $post, SocialAccount $account): array
    {
        $pid = $account->meta['page_id'] ?? $account->platform_account_id;
        $r = Http::post("https://graph.facebook.com/v18.0/{$pid}/feed", [
            'message' => $post->body,
            'access_token' => $account->token,
        ]);
        return [
            'success' => $r->successful(),
            'post_id' => $r->json()['id'] ?? null,
            'error' => $r->json()['error']['message'] ?? null,
        ];
    }

    public function publishToTwitter(Post $post, SocialAccount $account): array
    {
        $r = Http::withToken($account->token)->post('https://api.twitter.com/2/tweets', [
            'text' => mb_substr($post->body, 0, 280),
        ]);
        return [
            'success' => $r->successful(),
            'post_id' => $r->json()['data']['id'] ?? null,
            'error' => $r->json()['detail'] ?? null,
        ];
    }

    public function publishToLinkedIn(Post $post, SocialAccount $account): array
    {
        $r = Http::withToken($account->token)->post('https://api.linkedin.com/v2/ugcPosts', [
            'author' => "urn:li:person:{$account->platform_account_id}",
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $post->body],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ]);
        return [
            'success' => $r->successful(),
            'post_id' => $r->json()['id'] ?? null,
        ];
    }

    public function publishToInstagram(Post $post, SocialAccount $account): array
    {
        $media = $post->media_urls ?? [];
        if (empty($media)) {
            return ['success' => false, 'error' => 'IG requires image'];
        }
        $igId = $account->meta['ig_account_id'] ?? $account->platform_account_id;
        $container = Http::post("https://graph.facebook.com/v18.0/{$igId}/media", [
            'image_url' => $media[0],
            'caption' => $post->body,
            'access_token' => $account->token,
        ]);
        $cid = $container->json()['id'] ?? null;
        if (!$cid) {
            return ['success' => false, 'error' => 'Container failed'];
        }
        sleep(5);
        $pub = Http::post("https://graph.facebook.com/v18.0/{$igId}/media_publish", [
            'creation_id' => $cid,
            'access_token' => $account->token,
        ]);
        return [
            'success' => $pub->successful(),
            'post_id' => $pub->json()['id'] ?? null,
        ];
    }
}
