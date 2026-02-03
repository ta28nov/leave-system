<?php

return [
    'secret' => env('JWT_SECRET'),

    'keys' => [
        /*
        |--------------------------------------------------------------------------
        | Public Key
        |--------------------------------------------------------------------------
        |
        | A path or resource to your public key.
        |
        | E.g. 'file://path/to/public/key'
        |
        */

        'public' => env('JWT_PUBLIC_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Private Key
        |--------------------------------------------------------------------------
        |
        | A path or resource to your private key.
        |
        | E.g. 'file://path/to/private/key'
        |
        */

        'private' => env('JWT_PRIVATE_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Passphrase
        |--------------------------------------------------------------------------
        |
        | The passphrase for your private key. Can be null if none set.
        |
        */

        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    'ttl' => (int) env('JWT_TTL', 60),


    'refresh_iat' => env('JWT_REFRESH_IAT', false),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160),


    'algo' => env('JWT_ALGO', 'HS256'),


    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],


    'persistent_claims' => [
        // 'foo',
        // 'bar',
    ],


    'lock_subject' => true,


    'leeway' => (int) env('JWT_LEEWAY', 0),


    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 0),



    'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', true),



    'decrypt_cookies' => false,

    
    'cookie_key_name' => 'token',


    'providers' => [
        /*
        |--------------------------------------------------------------------------
        | JWT Provider
        |--------------------------------------------------------------------------
        |
        | Specify the provider that is used to create and decode the tokens.
        |
        */

        'jwt' => PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci::class,

        /*
        |--------------------------------------------------------------------------
        | Authentication Provider
        |--------------------------------------------------------------------------
        |
        | Specify the provider that is used to authenticate users.
        |
        */

        'auth' => PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate::class,

        /*
        |--------------------------------------------------------------------------
        | Storage Provider
        |--------------------------------------------------------------------------
        |
        | Specify the provider that is used to store tokens in the blacklist.
        |
        */

        'storage' => PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];
