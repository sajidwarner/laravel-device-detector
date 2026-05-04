<?php

namespace SajidWarner\LaraTrack\Middleware;

use Closure;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;
use Symfony\Component\HttpFoundation\Response;

class BlockCountries
{
    public function handle(Request $request, Closure $next, string ...$countries): Response
    {
        $location = LaraTrack::getLocation($request);
        $code     = strtoupper($location['country_code'] ?? '');

        $blocked = array_map('strtoupper', count($countries) ? $countries : config('laratrack.blocked_countries', []));

        if ($code && in_array($code, $blocked)) {
            return response(
                config('laratrack.middleware.country_message', 'Access denied: your country is not allowed.'),
                403
            );
        }

        return $next($request);
    }
}
