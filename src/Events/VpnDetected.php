<?php

namespace SajidWarner\LaraTrack\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class VpnDetected
{
    use Dispatchable;

    public function __construct(
        public readonly Request $request,
        public readonly string $ip,
        public readonly string $type, // 'vpn' | 'proxy'
    ) {}
}
