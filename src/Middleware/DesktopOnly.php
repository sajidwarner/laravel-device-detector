<?php

namespace SajidWarner\LaraTrack\Middleware;

use Closure;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;
use Symfony\Component\HttpFoundation\Response;

class DesktopOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!LaraTrack::isDesktop($request)) {
            $redirect = config('laratrack.middleware.mobile_redirect');
            return $redirect
                ? redirect($redirect)
                : response(config('laratrack.middleware.desktop_only_message', 'This page is only available on desktop.'), 403);
        }

        return $next($request);
    }
}
