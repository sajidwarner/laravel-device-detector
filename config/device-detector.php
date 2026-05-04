<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tor Detection Settings
    |--------------------------------------------------------------------------
    |
    | Configure Tor exit node detection. When enabled, the package will
    | fetch and cache a list of Tor exit nodes to identify Tor connections.
    |
    */

    'enable_tor_detection' => env('DEVICE_DETECTOR_TOR_DETECTION', true),

    'tor_cache_duration' => env('DEVICE_DETECTOR_TOR_CACHE', 3600), // 1 hour in seconds

    'tor_exit_node_url' => env('DEVICE_DETECTOR_TOR_URL', 'https://check.torproject.org/exit-addresses'),

    /*
    |--------------------------------------------------------------------------
    | Robot Detection Settings
    |--------------------------------------------------------------------------
    |
    | Enable or disable robot/bot detection. When enabled, the package will
    | identify common search engine crawlers and bots.
    |
    */

    'enable_robot_detection' => env('DEVICE_DETECTOR_ROBOT_DETECTION', true),

    /*
    |--------------------------------------------------------------------------
    | IP Geolocation Settings (ipgeolocation.io)
    |--------------------------------------------------------------------------
    |
    | Enable IP geolocation to get country, city, ISP, timezone, and more.
    | Get a free API key at https://ipgeolocation.io (30,000 req/month free).
    |
    */

    'enable_ip_geolocation' => env('DEVICE_DETECTOR_GEO_ENABLED', false),

    'ip_geolocation_api_key' => env('DEVICE_DETECTOR_GEO_API_KEY', ''),

    'ip_geolocation_api_url' => env('DEVICE_DETECTOR_GEO_URL', 'https://api.ipgeolocation.io/v3/ipgeo'),

    'ip_geolocation_cache_duration' => env('DEVICE_DETECTOR_GEO_CACHE', 3600),

];
