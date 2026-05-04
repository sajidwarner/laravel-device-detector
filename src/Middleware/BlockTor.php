<?php

namespace SajidWarner\LaraTrack\Middleware;

use Closure;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;
use Symfony\Component\HttpFoundation\Response;

class BlockTor
{
    public function handle(Request $request, Closure $next, int $status = 403): Response
    {
        if (LaraTrack::isTor($request)) {
            return response(
                config('laratrack.middleware.tor_message', 'Access denied: Tor connections are not allowed.'),
                $status
            );
        }

        return $next($request);
    }
}
