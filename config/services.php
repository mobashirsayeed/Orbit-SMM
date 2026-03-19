<?php

return [
    // ... existing config ...

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/facebook/callback',
        'graph_version' => 'v18.0',
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/twitter/callback',
        'bearer_token' => env('TWITTER_BEARER_TOKEN'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/linkedin/callback',
        'scopes' => ['r_liteprofile', 'w_member_social', 'r_emailaddress'],
    ],

    'instagram' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'), // Uses Facebook OAuth
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/instagram/callback',
    ],
];
