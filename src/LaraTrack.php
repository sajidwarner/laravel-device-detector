<?php

namespace SajidWarner\LaraTrack;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SajidWarner\LaraTrack\Events\BotDetected;
use SajidWarner\LaraTrack\Events\TorDetected;
use SajidWarner\LaraTrack\Events\VpnDetected;

class LaraTrack
{
    protected ?Request $request;
    protected array $detectionData = [];
    protected bool $isDetected = false;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public function detect(?Request $request = null): array
    {
        if ($request) {
            $this->request = $request;
            $this->isDetected = false;
        }

        if ($this->isDetected) {
            return $this->detectionData;
        }

        $userAgent = $this->getUserAgent();
        $ip        = $this->getIp();

        $isTor       = $this->detectTor($ip);
        $robotData   = $this->detectRobot($userAgent);
        $browserData = $this->detectBrowser($userAgent);
        $platform    = $this->detectPlatform($userAgent);
        $deviceData  = $this->detectDevice($userAgent);
        $geoData     = $this->getIpGeolocation($ip);
        $language    = $this->detectLanguage();

        $isVpn   = $geoData['is_vpn'] ?? false;
        $isProxy = $geoData['is_proxy'] ?? false;

        // Fire events
        if (config('laratrack.fire_events', true)) {
            if ($robotData['is_robot']) {
                BotDetected::dispatch($this->request, $robotData['name'] ?? 'Unknown', $ip);
            }
            if ($isTor) {
                TorDetected::dispatch($this->request, $ip);
            }
            if ($isVpn) {
                VpnDetected::dispatch($this->request, $ip, 'vpn');
            } elseif ($isProxy) {
                VpnDetected::dispatch($this->request, $ip, 'proxy');
            }
        }

        $location = collect($geoData)->except(['is_vpn', 'is_proxy', 'is_datacenter', 'threat_score'])->toArray();

        $this->detectionData = [
            'browser'         => $browserData['name'],
            'browser_version' => $browserData['version'],
            'platform'        => $platform,
            'device_type'     => $deviceData['type'],
            'device_brand'    => $deviceData['brand'],
            'device_model'    => $deviceData['model'],
            'is_mobile'       => $deviceData['is_mobile'],
            'is_tablet'       => $deviceData['is_tablet'],
            'is_desktop'      => $deviceData['is_desktop'],
            'is_robot'        => $robotData['is_robot'],
            'is_tor'          => $isTor,
            'is_vpn'          => $isVpn,
            'is_proxy'        => $isProxy,
            'robot_name'      => $robotData['name'],
            'language'        => $language,
            'ip'              => $ip,
            'location'        => $location,
        ];

        $this->isDetected = true;
        return $this->detectionData;
    }

    protected function getIpGeolocation(string $ip): array
    {
        if (!config('laratrack.enable_ip_geolocation', false)) {
            return [];
        }

        $apiKey = config('laratrack.ip_geolocation_api_key', '');
        if (empty($apiKey)) {
            return [];
        }

        $cacheKey = 'laratrack_geo_' . md5($ip);

        return Cache::remember($cacheKey, config('laratrack.ip_geolocation_cache_duration', 3600), function () use ($ip, $apiKey) {
            try {
                $response = Http::timeout(5)->get(
                    config('laratrack.ip_geolocation_api_url', 'https://api.ipgeolocation.io/v3/ipgeo'),
                    ['apiKey' => $apiKey, 'ip' => $ip]
                );

                if ($response->successful()) {
                    $geo      = $response->json();
                    $security = $geo['security'] ?? [];
                    return [
                        'country'      => $geo['country_name'] ?? null,
                        'country_code' => $geo['country_code2'] ?? null,
                        'city'         => $geo['city'] ?? null,
                        'state'        => $geo['state_prov'] ?? null,
                        'district'     => $geo['district'] ?? null,
                        'zip'          => $geo['zipcode'] ?? null,
                        'latitude'     => $geo['latitude'] ?? null,
                        'longitude'    => $geo['longitude'] ?? null,
                        'timezone'     => $geo['time_zone']['name'] ?? null,
                        'isp'          => $geo['isp'] ?? null,
                        'organization' => $geo['organization'] ?? null,
                        'currency'     => $geo['currency']['code'] ?? null,
                        'calling_code' => $geo['calling_code'] ?? null,
                        'is_eu'        => $geo['is_eu'] ?? false,
                        'is_vpn'       => $security['is_vpn'] ?? false,
                        'is_proxy'     => $security['is_proxy'] ?? false,
                        'is_datacenter'=> $security['is_datacenter'] ?? false,
                        'threat_score' => $security['threat_score'] ?? 0,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('LaraTrack: Failed to fetch IP geolocation: ' . $e->getMessage());
            }
            return [];
        });
    }

    protected function detectTor(string $ip): bool
    {
        if (!config('laratrack.enable_tor_detection', true)) {
            return false;
        }
        return in_array($ip, $this->getTorExitNodes());
    }

    protected function getTorExitNodes(): array
    {
        return Cache::remember('laratrack_tor_exit_nodes', config('laratrack.tor_cache_duration', 3600), function () {
            try {
                $response = Http::timeout(10)->get(
                    config('laratrack.tor_exit_node_url', 'https://check.torproject.org/exit-addresses')
                );
                if ($response->successful()) {
                    preg_match_all('/ExitAddress\s+([0-9\.]+)/', $response->body(), $matches);
                    return $matches[1] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning('LaraTrack: Failed to fetch Tor exit nodes: ' . $e->getMessage());
            }
            return [];
        });
    }

    protected function detectRobot(string $userAgent): array
    {
        if (!config('laratrack.enable_robot_detection', true)) {
            return ['is_robot' => false, 'name' => null];
        }

        $robots = [
            'Googlebot'      => '/googlebot/i',
            'Bingbot'        => '/bingbot/i',
            'Slurp'          => '/slurp/i',
            'DuckDuckBot'    => '/duckduckbot/i',
            'Baiduspider'    => '/baiduspider/i',
            'YandexBot'      => '/yandexbot/i',
            'Sogou'          => '/sogou/i',
            'Exabot'         => '/exabot/i',
            'facebot'        => '/facebot/i',
            'ia_archiver'    => '/ia_archiver/i',
            'Facebookbot'    => '/facebookexternalhit/i',
            'Twitterbot'     => '/twitterbot/i',
            'LinkedInBot'    => '/linkedinbot/i',
            'WhatsApp'       => '/whatsapp/i',
            'Telegram'       => '/telegrambot/i',
            'Discordbot'     => '/discordbot/i',
            'Slackbot'       => '/slackbot/i',
            'Applebot'       => '/applebot/i',
            'AhrefsBot'      => '/ahrefsbot/i',
            'SemrushBot'     => '/semrushbot/i',
            'MJ12bot'        => '/mj12bot/i',
            'DotBot'         => '/dotbot/i',
            'Screaming Frog' => '/screaming frog/i',
            'SEOkicks'       => '/seokicks/i',
        ];

        foreach ($robots as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return ['is_robot' => true, 'name' => $name];
            }
        }

        return ['is_robot' => false, 'name' => null];
    }

    protected function detectBrowser(string $userAgent): array
    {
        $secChUa        = strtolower($this->request->header('Sec-CH-UA', ''));
        $xRequestedWith = strtolower($this->request->header('X-Requested-With', ''));

        // X-Requested-With based detection for mobile apps (takes priority)
        if (!empty($xRequestedWith)) {
            if (str_contains($xRequestedWith, 'io.kahf.browser')) {
                return ['name' => 'Kahf Browser', 'version' => $this->extractVersion($userAgent, 'Kahf Browser')];
            }
            if (str_contains($xRequestedWith, 'com.duckduckgo.mobile')) {
                return ['name' => 'DuckDuckGo Browser', 'version' => $this->extractVersion($userAgent, 'DuckDuckGo Browser')];
            }
        }

        $browsers = [
            'Brave'              => ['/brave/i'],
            'Kahf Browser'       => ['/kahf/i'],
            'DuckDuckGo Browser' => ['/duckduckgo/i'],
            'Microsoft Edge'     => ['/edg\//i', '/edge\//i'],
            'Opera GX'           => ['/oprgx/i'],
            'Opera'              => ['/opr\//i', '/opera/i'],
            'Vivaldi'            => ['/vivaldi/i'],
            'Samsung Internet'   => ['/samsungbrowser/i'],
            'UC Browser'         => ['/ucbrowser/i'],
            // Tor Browser UA: (Windows NT X.X; rv:Y.0) with no Win64/x64 — unlike standard Firefox
            'Tor Browser'        => ['/\(windows nt \d+\.\d+; rv:\d+\.\d+\) gecko\/\d+ firefox/i'],
            'Google Chrome'      => ['/chrome/i'],
            'Safari'             => ['/safari/i'],
            'Firefox'            => ['/firefox/i'],
            'Internet Explorer'  => ['/msie|trident/i'],
            'Chromium'           => ['/chromium/i'],
        ];

        if (!empty($secChUa)) {
            foreach ($browsers as $name => $patterns) {
                foreach ($patterns as $pattern) {
                    if (str_contains($secChUa, strtolower(str_replace('/', '', $pattern)))) {
                        return ['name' => $name, 'version' => $this->extractVersion($userAgent, $name)];
                    }
                }
            }
        }

        foreach ($browsers as $name => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    return ['name' => $name, 'version' => $this->extractVersion($userAgent, $name)];
                }
            }
        }

        return ['name' => 'Unknown', 'version' => ''];
    }

    protected function extractVersion(string $userAgent, string $browser): string
    {
        $patterns = [
            'Kahf Browser'       => '/kahf\/([0-9\.]+)/i',
            'DuckDuckGo Browser' => '/duckduckgo\/([0-9\.]+)/i',
            'Tor Browser'        => '/firefox\/([0-9\.]+)/i',
            'Google Chrome'      => '/chrome\/([0-9\.]+)/i',
            'Firefox'            => '/firefox\/([0-9\.]+)/i',
            'Safari'             => '/version\/([0-9\.]+)/i',
            'Microsoft Edge'     => '/edg\/([0-9\.]+)/i',
            'Opera'              => '/opr\/([0-9\.]+)/i',
            'Opera GX'           => '/oprgx\/([0-9\.]+)/i',
            'Samsung Internet'   => '/samsungbrowser\/([0-9\.]+)/i',
            'Brave'              => '/chrome\/([0-9\.]+)/i',
            'Vivaldi'            => '/vivaldi\/([0-9\.]+)/i',
            'Internet Explorer'  => '/(?:msie |rv:)([0-9\.]+)/i',
        ];

        if (isset($patterns[$browser]) && preg_match($patterns[$browser], $userAgent, $matches)) {
            return $matches[1];
        }
        return '';
    }

    protected function detectPlatform(string $userAgent): string
    {
        $secChUaPlatform = $this->request->header('Sec-CH-UA-Platform', '');
        if (!empty($secChUaPlatform)) {
            return trim($secChUaPlatform, '"');
        }

        $platforms = [
            'Windows 10'    => '/windows nt 10/i',
            'Windows 8.1'   => '/windows nt 6\.3/i',
            'Windows 8'     => '/windows nt 6\.2/i',
            'Windows 7'     => '/windows nt 6\.1/i',
            'Windows Vista' => '/windows nt 6\.0/i',
            'Windows XP'    => '/windows nt 5\.1/i',
            'iOS (iPhone)'  => '/iphone/i',
            'iOS (iPad)'    => '/ipad/i',
            'iPadOS'        => '/macintosh.*ipad/i',
            'Android'       => '/android/i',
            'macOS'         => '/macintosh|mac os x/i',
            'Ubuntu'        => '/ubuntu/i',
            'Linux'         => '/linux/i',
            'Chrome OS'     => '/cros/i',
            'BlackBerry'    => '/blackberry/i',
            'Windows Phone' => '/windows phone/i',
        ];

        foreach ($platforms as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }
        return 'Unknown OS';
    }

    protected function detectDevice(string $userAgent): array
    {
        $isMobile  = $this->isMobileDevice($userAgent);
        $isTablet  = $this->isTabletDevice($userAgent);
        $isDesktop = !$isMobile && !$isTablet;

        $brand = null;
        $model = null;

        if ($isMobile || $isTablet) {
            $deviceInfo = $this->detectMobileDevice($userAgent);
            $brand = $deviceInfo['brand'];
            $model = $deviceInfo['model'];
        }

        return [
            'type'       => $isTablet ? 'tablet' : ($isMobile ? 'mobile' : 'desktop'),
            'brand'      => $brand,
            'model'      => $model,
            'is_mobile'  => $isMobile && !$isTablet,
            'is_tablet'  => $isTablet,
            'is_desktop' => $isDesktop,
        ];
    }

    protected function isMobileDevice(string $userAgent): bool
    {
        return preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent) === 1;
    }

    protected function isTabletDevice(string $userAgent): bool
    {
        return preg_match('/tablet|ipad|playbook|silk|kindle/i', $userAgent) === 1;
    }

    protected function detectMobileDevice(string $userAgent): array
    {
        if (preg_match('/iphone/i', $userAgent)) {
            return ['brand' => 'Apple', 'model' => $this->extractIphoneModel($userAgent) ?: 'iPhone'];
        }
        if (preg_match('/ipad/i', $userAgent)) {
            return ['brand' => 'Apple', 'model' => 'iPad'];
        }
        if (preg_match('/samsung|sm-|galaxy/i', $userAgent)) {
            if (preg_match('/sm-([a-z0-9]+)/i', $userAgent, $m)) return ['brand' => 'Samsung', 'model' => 'SM-' . strtoupper($m[1])];
            if (preg_match('/galaxy\s*([a-z0-9\s]+)/i', $userAgent, $m)) return ['brand' => 'Samsung', 'model' => 'Galaxy ' . trim($m[1])];
            return ['brand' => 'Samsung', 'model' => null];
        }
        if (preg_match('/xiaomi|redmi|mi\s|pocophone/i', $userAgent)) {
            if (preg_match('/(redmi|mi|pocophone)\s*([a-z0-9\s]+)/i', $userAgent, $m)) return ['brand' => 'Xiaomi', 'model' => ucfirst($m[1]) . ' ' . trim($m[2])];
            return ['brand' => 'Xiaomi', 'model' => null];
        }
        if (preg_match('/huawei|honor/i', $userAgent)) {
            if (preg_match('/(huawei|honor)[\s-]([a-z0-9\s]+)/i', $userAgent, $m)) return ['brand' => 'Huawei', 'model' => ucfirst($m[1]) . ' ' . trim($m[2])];
            return ['brand' => 'Huawei', 'model' => null];
        }
        if (preg_match('/oneplus/i', $userAgent)) {
            if (preg_match('/oneplus\s*([a-z0-9]+)/i', $userAgent, $m)) return ['brand' => 'OnePlus', 'model' => 'OnePlus ' . $m[1]];
            return ['brand' => 'OnePlus', 'model' => null];
        }
        if (preg_match('/oppo/i', $userAgent)) {
            if (preg_match('/oppo\s*([a-z0-9]+)/i', $userAgent, $m)) return ['brand' => 'Oppo', 'model' => 'Oppo ' . $m[1]];
            return ['brand' => 'Oppo', 'model' => null];
        }
        if (preg_match('/vivo/i', $userAgent)) {
            if (preg_match('/vivo\s*([a-z0-9]+)/i', $userAgent, $m)) return ['brand' => 'Vivo', 'model' => 'Vivo ' . $m[1]];
            return ['brand' => 'Vivo', 'model' => null];
        }
        if (preg_match('/pixel/i', $userAgent)) {
            if (preg_match('/pixel\s*([0-9a-z\s]+)/i', $userAgent, $m)) return ['brand' => 'Google', 'model' => 'Pixel ' . trim($m[1])];
            return ['brand' => 'Google', 'model' => 'Pixel'];
        }
        if (preg_match('/motorola|moto/i', $userAgent)) {
            if (preg_match('/moto\s*([a-z0-9\s]+)/i', $userAgent, $m)) return ['brand' => 'Motorola', 'model' => 'Moto ' . trim($m[1])];
            return ['brand' => 'Motorola', 'model' => null];
        }
        if (preg_match('/nokia/i', $userAgent)) {
            if (preg_match('/nokia\s*([0-9\.]+)/i', $userAgent, $m)) return ['brand' => 'Nokia', 'model' => 'Nokia ' . $m[1]];
            return ['brand' => 'Nokia', 'model' => null];
        }
        if (preg_match('/lg[\s-]/i', $userAgent)) {
            if (preg_match('/lg[\s-]([a-z0-9]+)/i', $userAgent, $m)) return ['brand' => 'LG', 'model' => 'LG ' . strtoupper($m[1])];
            return ['brand' => 'LG', 'model' => null];
        }
        if (preg_match('/sony/i', $userAgent)) {
            if (preg_match('/sony\s*([a-z0-9\s]+)/i', $userAgent, $m)) return ['brand' => 'Sony', 'model' => 'Sony ' . trim($m[1])];
            return ['brand' => 'Sony', 'model' => null];
        }
        if (preg_match('/htc/i', $userAgent)) {
            if (preg_match('/htc\s*([a-z0-9\s]+)/i', $userAgent, $m)) return ['brand' => 'HTC', 'model' => 'HTC ' . trim($m[1])];
            return ['brand' => 'HTC', 'model' => null];
        }
        return ['brand' => null, 'model' => null];
    }

    protected function extractIphoneModel(string $userAgent): ?string
    {
        if (preg_match('/iphone\s*([0-9]+[,\.]?[0-9]*)/i', $userAgent, $matches)) {
            return 'iPhone ' . str_replace(',', '.', $matches[1]);
        }
        return null;
    }

    protected function detectLanguage(): string
    {
        $header = $this->request->header('Accept-Language', '');
        if (empty($header)) {
            return 'Unknown';
        }

        // Parse "en-US,en;q=0.9,bn;q=0.8" → "en-US"
        $parts = explode(',', $header);
        $primary = trim(explode(';', $parts[0])[0]);
        return $primary ?: 'Unknown';
    }

    protected function getUserAgent(): string
    {
        return strtolower($this->request->header('User-Agent', ''));
    }

    protected function getIp(): string
    {
        return $this->request->header('X-Real-IP', $this->request->ip());
    }

    public function getBrowser(?Request $request = null): string    { return $this->detect($request)['browser']; }
    public function getPlatform(?Request $request = null): string   { return $this->detect($request)['platform']; }
    public function getDeviceType(?Request $request = null): string { return $this->detect($request)['device_type']; }
    public function isMobile(?Request $request = null): bool        { return $this->detect($request)['is_mobile']; }
    public function isTablet(?Request $request = null): bool        { return $this->detect($request)['is_tablet']; }
    public function isDesktop(?Request $request = null): bool       { return $this->detect($request)['is_desktop']; }
    public function isRobot(?Request $request = null): bool         { return $this->detect($request)['is_robot']; }
    public function isTor(?Request $request = null): bool           { return $this->detect($request)['is_tor']; }
    public function isVpn(?Request $request = null): bool           { return $this->detect($request)['is_vpn']; }
    public function isProxy(?Request $request = null): bool         { return $this->detect($request)['is_proxy']; }
    public function getLanguage(?Request $request = null): string   { return $this->detect($request)['language']; }
    public function getLocation(?Request $request = null): array    { return $this->detect($request)['location'] ?? []; }
}
