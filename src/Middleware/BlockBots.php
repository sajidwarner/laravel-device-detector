<?php

namespace SajidWarner\LaraTrack\Middleware;

use Closure;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;
use Symfony\Component\HttpFoundation\Response;

class BlockBots
{
    public function handle(Request $request, Closure $next, int $status = 403): Response
    {
        if (LaraTrack::isRobot($request)) {
            return response(
                config('laratrack.middleware.bot_message', 'Access denied: bots are not allowed.'),
                $status
            );
        }

        return $next($request);
    }
}
