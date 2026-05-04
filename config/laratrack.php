<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tor Detection
    |--------------------------------------------------------------------------
    */
    'enable_tor_detection' => env('LARATRACK_TOR_DETECTION', true),
    'tor_cache_duration'   => env('LARATRACK_TOR_CACHE', 3600),
    'tor_exit_node_url'    => env('LARATRACK_TOR_URL', 'https://check.torproject.org/exit-addresses'),

    /*
    |--------------------------------------------------------------------------
    | Robot Detection
    |--------------------------------------------------------------------------
    */
    'enable_robot_detection' => env('LARATRACK_ROBOT_DETECTION', true),

    /*
    |--------------------------------------------------------------------------
    | IP Geolocation (ipgeolocation.io)
    | Free API key: https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY
    | Free plan: 30,000 requests/month
    |--------------------------------------------------------------------------
    */
    'enable_ip_geolocation'         => env('LARATRACK_GEO_ENABLED', false),
    'ip_geolocation_api_key'        => env('LARATRACK_GEO_API_KEY', ''),
    'ip_geolocation_api_url'        => env('LARATRACK_GEO_URL', 'https://api.ipgeolocation.io/v3/ipgeo'),
    'ip_geolocation_cache_duration' => env('LARATRACK_GEO_CACHE', 3600),

    /*
    |--------------------------------------------------------------------------
    | Events
    | Fire Laravel events when bots, Tor, or VPN are detected.
    | Listen to: BotDetected, TorDetected, VpnDetected
    |--------------------------------------------------------------------------
    */
    'fire_events' => env('LARATRACK_FIRE_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Country Blocking
    | ISO 2-letter country codes to block by default (used by BlockCountries middleware).
    | Can also pass countries inline: Route::middleware('laratrack.block-countries:CN,RU')
    |--------------------------------------------------------------------------
    */
    'blocked_countries' => [],

    /*
    |--------------------------------------------------------------------------
    | Middleware Messages & Redirects
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'bot_message'          => 'Access denied: bots are not allowed.',
        'tor_message'          => 'Access denied: Tor connections are not allowed.',
        'vpn_message'          => 'Access denied: VPN/Proxy connections are not allowed.',
        'country_message'      => 'Access denied: your country is not allowed.',
        'mobile_only_message'  => 'This page is only available on mobile devices.',
        'desktop_only_message' => 'This page is only available on desktop.',
        'mobile_redirect'      => null, // URL to redirect non-mobile users (DesktopOnly middleware)
        'desktop_redirect'     => null, // URL to redirect non-desktop users (MobileOnly middleware)
    ],

];
