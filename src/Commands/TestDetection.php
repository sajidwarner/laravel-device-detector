<?php

namespace SajidWarner\LaraTrack\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\LaraTrack;

class TestDetection extends Command
{
    protected $signature   = 'laratrack:test {ua? : User-Agent string to test} {--ip= : IP address to test}';
    protected $description = 'Test LaraTrack detection for a given User-Agent and IP';

    public function handle(): void
    {
        $ua = $this->argument('ua') ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $ip = $this->option('ip') ?? '127.0.0.1';

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', $ua);
        $request->headers->set('X-Real-IP', $ip);

        $detector = new LaraTrack($request);
        $result   = $detector->detect();

        $this->info('LaraTrack Detection Result');
        $this->line('─────────────────────────────────');

        $this->table(['Field', 'Value'], [
            ['Browser',       $result['browser'] . ' ' . $result['browser_version']],
            ['Platform',      $result['platform']],
            ['Device Type',   $result['device_type']],
            ['Device Brand',  $result['device_brand'] ?? '—'],
            ['Device Model',  $result['device_model'] ?? '—'],
            ['Is Mobile',     $result['is_mobile'] ? 'Yes' : 'No'],
            ['Is Tablet',     $result['is_tablet'] ? 'Yes' : 'No'],
            ['Is Desktop',    $result['is_desktop'] ? 'Yes' : 'No'],
            ['Is Robot',      $result['is_robot'] ? 'Yes — ' . $result['robot_name'] : 'No'],
            ['Is Tor',        $result['is_tor'] ? 'Yes' : 'No'],
            ['Is VPN',        $result['is_vpn'] ? 'Yes' : 'No'],
            ['Is Proxy',      $result['is_proxy'] ? 'Yes' : 'No'],
            ['Language',      $result['language'] ?? '—'],
            ['IP',            $result['ip']],
        ]);

        if (!empty($result['location'])) {
            $this->line('');
            $this->info('Geolocation');
            $this->line('─────────────────────────────────');
            $loc = $result['location'];
            $this->table(['Field', 'Value'], [
                ['Country',   ($loc['country'] ?? '—') . ' (' . ($loc['country_code'] ?? '—') . ')'],
                ['City',      $loc['city'] ?? '—'],
                ['State',     $loc['state'] ?? '—'],
                ['Timezone',  $loc['timezone'] ?? '—'],
                ['ISP',       $loc['isp'] ?? '—'],
                ['Currency',  $loc['currency'] ?? '—'],
            ]);
        }
    }
}
