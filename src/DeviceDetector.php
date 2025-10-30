<?php

namespace SajidWarner\DeviceDetector;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DeviceDetector
{
    protected ?Request $request;
    protected array $detectionData = [];
    protected bool $isDetected = false;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    /**
     * Main detection method
     */
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
        $ip = $this->getIp();

        // Detect Tor first
        $isTor = $this->detectTor($ip);

        // Detect Robot
        $robotData = $this->detectRobot($userAgent);

        // Detect Browser
        $browserData = $this->detectBrowser($userAgent);

        // Detect Platform
        $platform = $this->detectPlatform($userAgent);

        // Detect Device Type and Details
        $deviceData = $this->detectDevice($userAgent);

        $this->detectionData = [
            'browser' => $browserData['name'],
            'browser_version' => $browserData['version'],
            'platform' => $platform,
            'device_type' => $deviceData['type'],
            'device_brand' => $deviceData['brand'],
            'device_model' => $deviceData['model'],
            'is_mobile' => $deviceData['is_mobile'],
            'is_tablet' => $deviceData['is_tablet'],
            'is_desktop' => $deviceData['is_desktop'],
            'is_robot' => $robotData['is_robot'],
            'is_tor' => $isTor,
            'robot_name' => $robotData['name'],
            'ip' => $ip
        ];

        $this->isDetected = true;
        return $this->detectionData;
    }

    /**
     * Detect Tor connections
     */
    protected function detectTor(string $ip): bool
    {
        if (!config('device-detector.enable_tor_detection', true)) {
            return false;
        }

        $torExitNodes = $this->getTorExitNodes();
        return in_array($ip, $torExitNodes);
    }

    /**
     * Fetch Tor exit nodes with caching
     */
    protected function getTorExitNodes(): array
    {
        $cacheDuration = config('device-detector.tor_cache_duration', 3600);
        $torUrl = config('device-detector.tor_exit_node_url', 'https://check.torproject.org/exit-addresses');

        return Cache::remember('device_detector_tor_exit_nodes', $cacheDuration, function () use ($torUrl) {
            try {
                $response = Http::timeout(10)->get($torUrl);
                if ($response->successful()) {
                    preg_match_all('/ExitAddress\s+([0-9\.]+)/', $response->body(), $matches);
                    return $matches[1] ?? [];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch Tor exit nodes: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Detect robots/bots
     */
    protected function detectRobot(string $userAgent): array
    {
        if (!config('device-detector.enable_robot_detection', true)) {
            return ['is_robot' => false, 'name' => null];
        }

        $robots = [
            'Googlebot' => '/googlebot/i',
            'Bingbot' => '/bingbot/i',
            'Slurp' => '/slurp/i',
            'DuckDuckBot' => '/duckduckbot/i',
            'Baiduspider' => '/baiduspider/i',
            'YandexBot' => '/yandexbot/i',
            'Sogou' => '/sogou/i',
            'Exabot' => '/exabot/i',
            'facebot' => '/facebot/i',
            'ia_archiver' => '/ia_archiver/i',
            'Facebookbot' => '/facebookexternalhit/i',
            'Twitterbot' => '/twitterbot/i',
            'LinkedInBot' => '/linkedinbot/i',
            'WhatsApp' => '/whatsapp/i',
            'Telegram' => '/telegrambot/i',
            'Discordbot' => '/discordbot/i',
            'Slackbot' => '/slackbot/i',
            'Applebot' => '/applebot/i',
            'AhrefsBot' => '/ahrefsbot/i',
            'SemrushBot' => '/semrushbot/i',
            'MJ12bot' => '/mj12bot/i',
            'DotBot' => '/dotbot/i',
            'Screaming Frog' => '/screaming frog/i',
            'SEOkicks' => '/seokicks/i',
        ];

        foreach ($robots as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return ['is_robot' => true, 'name' => $name];
            }
        }

        return ['is_robot' => false, 'name' => null];
    }

    /**
     * Detect browser and version
     */
    protected function detectBrowser(string $userAgent): array
    {
        $secChUa = strtolower($this->request->header('Sec-CH-UA', ''));

        $browsers = [
            'Brave' => ['/brave/i', '/Brave/'],
            'Kahf' => ['/kahf/i'],
            'Microsoft Edge' => ['/edg\//i', '/edge\//i'],
            'Opera GX' => ['/oprgx/i'],
            'Opera' => ['/opr\//i', '/opera/i'],
            'Vivaldi' => ['/vivaldi/i'],
            'Samsung Internet' => ['/samsungbrowser/i'],
            'UC Browser' => ['/ucbrowser/i'],
            'Google Chrome' => ['/chrome/i'],
            'Safari' => ['/safari/i'],
            'Firefox' => ['/firefox/i'],
            'Internet Explorer' => ['/msie|trident/i'],
            'Tor Browser' => ['/tor/i'],
            'Chromium' => ['/chromium/i'],
        ];

        // Try Sec-CH-UA header first
        if (!empty($secChUa)) {
            foreach ($browsers as $name => $patterns) {
                foreach ($patterns as $pattern) {
                    if (str_contains($secChUa, strtolower(str_replace('/', '', $pattern)))) {
                        return [
                            'name' => $name,
                            'version' => $this->extractVersion($userAgent, $name)
                        ];
                    }
                }
            }
        }

        // Fallback to User-Agent
        foreach ($browsers as $name => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    return [
                        'name' => $name,
                        'version' => $this->extractVersion($userAgent, $name)
                    ];
                }
            }
        }

        return ['name' => 'Unknown', 'version' => ''];
    }

    /**
     * Extract browser version
     */
    protected function extractVersion(string $userAgent, string $browser): string
    {
        $patterns = [
            'Kahf' => '/kahf\/([0-9\.]+)/i',
            'Google Chrome' => '/chrome\/([0-9\.]+)/i',
            'Firefox' => '/firefox\/([0-9\.]+)/i',
            'Safari' => '/version\/([0-9\.]+)/i',
            'Microsoft Edge' => '/edg\/([0-9\.]+)/i',
            'Opera' => '/opr\/([0-9\.]+)/i',
            'Samsung Internet' => '/samsungbrowser\/([0-9\.]+)/i',
            'Brave' => '/chrome\/([0-9\.]+)/i',
            'Vivaldi' => '/vivaldi\/([0-9\.]+)/i',
        ];

        if (isset($patterns[$browser]) && preg_match($patterns[$browser], $userAgent, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Detect platform/OS
     */
    protected function detectPlatform(string $userAgent): string
    {
        $secChUaPlatform = $this->request->header('Sec-CH-UA-Platform', '');
        if (!empty($secChUaPlatform)) {
            return trim($secChUaPlatform, '"');
        }

        $platforms = [
            'Windows 11' => '/windows nt 10\.0.*; win64.*; x64.*; (rv|edge|edg)/i',
            'Windows 10' => '/windows nt 10/i',
            'Windows 8.1' => '/windows nt 6\.3/i',
            'Windows 8' => '/windows nt 6\.2/i',
            'Windows 7' => '/windows nt 6\.1/i',
            'Windows Vista' => '/windows nt 6\.0/i',
            'Windows XP' => '/windows nt 5\.1/i',
            'iOS (iPhone)' => '/iphone/i',
            'iOS (iPad)' => '/ipad/i',
            'iPadOS' => '/macintosh.*ipad/i',
            'Android' => '/android/i',
            'macOS' => '/macintosh|mac os x/i',
            'Ubuntu' => '/ubuntu/i',
            'Linux' => '/linux/i',
            'Chrome OS' => '/cros/i',
            'BlackBerry' => '/blackberry/i',
            'Windows Phone' => '/windows phone/i',
        ];

        foreach ($platforms as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return 'Unknown OS';
    }

    /**
     * Detect device type, brand, and model
     */
    protected function detectDevice(string $userAgent): array
    {
        $isMobile = $this->isMobileDevice($userAgent);
        $isTablet = $this->isTabletDevice($userAgent);
        $isDesktop = !$isMobile && !$isTablet;

        $brand = null;
        $model = null;

        if ($isMobile || $isTablet) {
            $deviceInfo = $this->detectMobileDevice($userAgent);
            $brand = $deviceInfo['brand'];
            $model = $deviceInfo['model'];
        }

        return [
            'type' => $isTablet ? 'tablet' : ($isMobile ? 'mobile' : 'desktop'),
            'brand' => $brand,
            'model' => $model,
            'is_mobile' => $isMobile,
            'is_tablet' => $isTablet,
            'is_desktop' => $isDesktop,
        ];
    }

    /**
     * Check if mobile device
     */
    protected function isMobileDevice(string $userAgent): bool
    {
        return preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent) === 1;
    }

    /**
     * Check if tablet device
     */
    protected function isTabletDevice(string $userAgent): bool
    {
        return preg_match('/tablet|ipad|playbook|silk|kindle/i', $userAgent) === 1;
    }

    /**
     * Detect mobile device brand and model
     */
    protected function detectMobileDevice(string $userAgent): array
    {
        // iPhone/iPad
        if (preg_match('/iphone/i', $userAgent)) {
            $model = $this->extractIphoneModel($userAgent);
            return ['brand' => 'Apple', 'model' => $model ?: 'iPhone'];
        }

        if (preg_match('/ipad/i', $userAgent)) {
            return ['brand' => 'Apple', 'model' => 'iPad'];
        }

        // Samsung
        if (preg_match('/samsung|sm-|galaxy/i', $userAgent)) {
            if (preg_match('/sm-([a-z0-9]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Samsung', 'model' => 'SM-' . strtoupper($matches[1])];
            }
            if (preg_match('/galaxy\s*([a-z0-9\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Samsung', 'model' => 'Galaxy ' . trim($matches[1])];
            }
            return ['brand' => 'Samsung', 'model' => null];
        }

        // Xiaomi
        if (preg_match('/xiaomi|redmi|mi\s|pocophone/i', $userAgent)) {
            if (preg_match('/(redmi|mi|pocophone)\s*([a-z0-9\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Xiaomi', 'model' => ucfirst($matches[1]) . ' ' . trim($matches[2])];
            }
            return ['brand' => 'Xiaomi', 'model' => null];
        }

        // Huawei
        if (preg_match('/huawei|honor/i', $userAgent)) {
            if (preg_match('/(huawei|honor)[\s-]([a-z0-9\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Huawei', 'model' => ucfirst($matches[1]) . ' ' . trim($matches[2])];
            }
            return ['brand' => 'Huawei', 'model' => null];
        }

        // OnePlus
        if (preg_match('/oneplus/i', $userAgent)) {
            if (preg_match('/oneplus\s*([a-z0-9]+)/i', $userAgent, $matches)) {
                return ['brand' => 'OnePlus', 'model' => 'OnePlus ' . $matches[1]];
            }
            return ['brand' => 'OnePlus', 'model' => null];
        }

        // Oppo
        if (preg_match('/oppo/i', $userAgent)) {
            if (preg_match('/oppo\s*([a-z0-9]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Oppo', 'model' => 'Oppo ' . $matches[1]];
            }
            return ['brand' => 'Oppo', 'model' => null];
        }

        // Vivo
        if (preg_match('/vivo/i', $userAgent)) {
            if (preg_match('/vivo\s*([a-z0-9]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Vivo', 'model' => 'Vivo ' . $matches[1]];
            }
            return ['brand' => 'Vivo', 'model' => null];
        }

        // Google Pixel
        if (preg_match('/pixel/i', $userAgent)) {
            if (preg_match('/pixel\s*([0-9a-z\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Google', 'model' => 'Pixel ' . trim($matches[1])];
            }
            return ['brand' => 'Google', 'model' => 'Pixel'];
        }

        // Motorola
        if (preg_match('/motorola|moto/i', $userAgent)) {
            if (preg_match('/moto\s*([a-z0-9\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Motorola', 'model' => 'Moto ' . trim($matches[1])];
            }
            return ['brand' => 'Motorola', 'model' => null];
        }

        // Nokia
        if (preg_match('/nokia/i', $userAgent)) {
            if (preg_match('/nokia\s*([0-9\.]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Nokia', 'model' => 'Nokia ' . $matches[1]];
            }
            return ['brand' => 'Nokia', 'model' => null];
        }

        // LG
        if (preg_match('/lg[\s-]/i', $userAgent)) {
            if (preg_match('/lg[\s-]([a-z0-9]+)/i', $userAgent, $matches)) {
                return ['brand' => 'LG', 'model' => 'LG ' . strtoupper($matches[1])];
            }
            return ['brand' => 'LG', 'model' => null];
        }

        // Sony
        if (preg_match('/sony/i', $userAgent)) {
            if (preg_match('/sony\s*([a-z0-9\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'Sony', 'model' => 'Sony ' . trim($matches[1])];
            }
            return ['brand' => 'Sony', 'model' => null];
        }

        // HTC
        if (preg_match('/htc/i', $userAgent)) {
            if (preg_match('/htc\s*([a-z0-9\s]+)/i', $userAgent, $matches)) {
                return ['brand' => 'HTC', 'model' => 'HTC ' . trim($matches[1])];
            }
            return ['brand' => 'HTC', 'model' => null];
        }

        return ['brand' => null, 'model' => null];
    }

    /**
     * Extract iPhone model
     */
    protected function extractIphoneModel(string $userAgent): ?string
    {
        if (preg_match('/iphone\s*([0-9]+[,\.]?[0-9]*)/i', $userAgent, $matches)) {
            return 'iPhone ' . str_replace(',', '.', $matches[1]);
        }
        return null;
    }

    /**
     * Get user agent string
     */
    protected function getUserAgent(): string
    {
        return strtolower($this->request->header('User-Agent', ''));
    }

    /**
     * Get IP address
     */
    protected function getIp(): string
    {
        return $this->request->header('X-Real-IP', $this->request->ip());
    }

    /**
     * Public helper methods
     */
    public function getBrowser(?Request $request = null): string
    {
        $data = $this->detect($request);
        return $data['browser'];
    }

    public function getPlatform(?Request $request = null): string
    {
        $data = $this->detect($request);
        return $data['platform'];
    }

    public function getDeviceType(?Request $request = null): string
    {
        $data = $this->detect($request);
        return $data['device_type'];
    }

    public function isMobile(?Request $request = null): bool
    {
        $data = $this->detect($request);
        return $data['is_mobile'];
    }

    public function isTablet(?Request $request = null): bool
    {
        $data = $this->detect($request);
        return $data['is_tablet'];
    }

    public function isDesktop(?Request $request = null): bool
    {
        $data = $this->detect($request);
        return $data['is_desktop'];
    }

    public function isRobot(?Request $request = null): bool
    {
        $data = $this->detect($request);
        return $data['is_robot'];
    }

    public function isTor(?Request $request = null): bool
    {
        $data = $this->detect($request);
        return $data['is_tor'];
    }
}
