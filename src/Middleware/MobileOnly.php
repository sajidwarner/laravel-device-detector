<?php

namespace SajidWarner\LaraTrack\Middleware;

use Closure;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;
use Symfony\Component\HttpFoundation\Response;

class MobileOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!LaraTrack::isMobile($request)) {
            $redirect = config('laratrack.middleware.desktop_redirect');
            return $redirect
                ? redirect($redirect)
                : response(config('laratrack.middleware.mobile_only_message', 'This page is only available on mobile devices.'), 403);
        }

        return $next($request);
    }
}
