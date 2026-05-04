<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tor Detection Settings
    |--------------------------------------------------------------------------
    */

    'enable_tor_detection' => env('LARATRACK_TOR_DETECTION', true),

    'tor_cache_duration'   => env('LARATRACK_TOR_CACHE', 3600),

    'tor_exit_node_url'    => env('LARATRACK_TOR_URL', 'https://check.torproject.org/exit-addresses'),

    /*
    |--------------------------------------------------------------------------
    | Robot Detection Settings
    |--------------------------------------------------------------------------
    */

    'enable_robot_detection' => env('LARATRACK_ROBOT_DETECTION', true),

    /*
    |--------------------------------------------------------------------------
    | IP Geolocation Settings (ipgeolocation.io)
    |--------------------------------------------------------------------------
    |
    | Get a free API key at https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY
    | Free plan: 30,000 requests/month — no credit card required.
    |
    */

    'enable_ip_geolocation'         => env('LARATRACK_GEO_ENABLED', false),

    'ip_geolocation_api_key'        => env('LARATRACK_GEO_API_KEY', ''),

    'ip_geolocation_api_url'        => env('LARATRACK_GEO_URL', 'https://api.ipgeolocation.io/v3/ipgeo'),

    'ip_geolocation_cache_duration' => env('LARATRACK_GEO_CACHE', 3600),

];
