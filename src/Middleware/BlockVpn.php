<?php

namespace SajidWarner\LaraTrack\Middleware;

use Closure;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;
use Symfony\Component\HttpFoundation\Response;

class BlockVpn
{
    public function handle(Request $request, Closure $next, int $status = 403): Response
    {
        if (LaraTrack::isVpn($request) || LaraTrack::isProxy($request)) {
            return response(
                config('laratrack.middleware.vpn_message', 'Access denied: VPN/Proxy connections are not allowed.'),
                $status
            );
        }

        return $next($request);
    }
}
