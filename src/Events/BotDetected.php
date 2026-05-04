<?php

namespace SajidWarner\LaraTrack\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class BotDetected
{
    use Dispatchable;

    public function __construct(
        public readonly Request $request,
        public readonly string $botName,
        public readonly string $ip,
    ) {}
}
