<?php

namespace SajidWarner\LaraTrack\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCache extends Command
{
    protected $signature   = 'laratrack:clear-cache';
    protected $description = 'Clear all LaraTrack cached data (Tor nodes, geolocation)';

    public function handle(): void
    {
        Cache::forget('laratrack_tor_exit_nodes');
        $this->info('LaraTrack cache cleared successfully.');
    }
}
