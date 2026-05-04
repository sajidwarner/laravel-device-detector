# Laravel Device Detector

A comprehensive Laravel package for detecting browsers, devices, robots, platforms, and Tor connections with advanced user agent parsing.

## Features

- 🌐 **Browser Detection**: Chrome, Firefox, Safari, Edge, Opera, Brave, Tor, and more
- 📱 **Mobile Device Detection**: Detect mobile brands and models (Samsung, iPhone, Xiaomi, etc.)
- 💻 **Desktop Detection**: Identify desktop browsers and operating systems
- 🤖 **Robot/Bot Detection**: Identify search engine crawlers and bots
- 🔒 **Tor Detection**: Real-time Tor exit node detection with caching
- 🖥️ **Platform Detection**: Windows, macOS, Linux, Android, iOS, etc.
- 📊 **Tablet Detection**: Identify tablets and their models
- ⚡ **Performance**: Cached Tor nodes, optimized regex patterns
- 🎯 **Client Hints Support**: Uses modern Sec-CH-UA headers

## Installation

Install via Composer:

```bash
composer require sajidwarner/laravel-device-detector
```

### Laravel Auto-Discovery

The package will automatically register the service provider and facade.

### Manual Registration (Optional)

If auto-discovery is disabled, add to `config/app.php`:

```php
'providers' => [
    SajidWarner\DeviceDetector\DeviceDetectorServiceProvider::class,
],

'aliases' => [
    'DeviceDetector' => SajidWarner\DeviceDetector\Facades\DeviceDetector::class,
],
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="SajidWarner\DeviceDetector\DeviceDetectorServiceProvider"
```

## Usage

### Basic Usage

```php
use SajidWarner\DeviceDetector\Facades\DeviceDetector;

// Detect current request
$device = DeviceDetector::detect();

// Get specific information
$browser = DeviceDetector::getBrowser();
$platform = DeviceDetector::getPlatform();
$deviceType = DeviceDetector::getDeviceType();
$isMobile = DeviceDetector::isMobile();
$isTablet = DeviceDetector::isTablet();
$isDesktop = DeviceDetector::isDesktop();
$isRobot = DeviceDetector::isRobot();
$isTor = DeviceDetector::isTor();
```

### Full Detection Array

```php
$data = DeviceDetector::detect();

/*
Returns:
[
    'browser'        => 'Google Chrome',
    'browser_version'=> '120.0',
    'platform'       => 'Windows 10',
    'device_type'    => 'desktop',
    'device_brand'   => null,
    'device_model'   => null,
    'is_mobile'      => false,
    'is_tablet'      => false,
    'is_desktop'     => true,
    'is_robot'       => false,
    'is_tor'         => false,
    'robot_name'     => null,
    'ip'             => '192.168.1.1',
    'location'       => [               // only when geolocation is enabled
        'country'      => 'Bangladesh',
        'country_code' => 'BD',
        'city'         => 'Dhaka',
        'state'        => 'Dhaka Division',
        'district'     => 'Dhaka',
        'zip'          => '1000',
        'latitude'     => '23.72305',
        'longitude'    => '90.40860',
        'timezone'     => 'Asia/Dhaka',
        'isp'          => 'Ranks ITT',
        'organization' => 'AS24323 Ranks ITT',
        'currency'     => 'BDT',
        'calling_code' => '+880',
        'is_eu'        => false,
    ],
]
*/
```

### In Controllers

```php
use Illuminate\Http\Request;
use SajidWarner\DeviceDetector\Facades\DeviceDetector;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $device = DeviceDetector::detect($request);
        
        if ($device['is_mobile']) {
            return view('mobile.home', compact('device'));
        }
        
        if ($device['is_robot']) {
            return response('Bot detected: ' . $device['robot_name']);
        }
        
        return view('home', compact('device'));
    }
}
```

### Middleware Usage

Create a middleware to block Tor or bots:

```php
php artisan make:middleware BlockTor
```

```php
use SajidWarner\DeviceDetector\Facades\DeviceDetector;

public function handle($request, Closure $next)
{
    if (DeviceDetector::isTor($request)) {
        return response('Tor connections not allowed', 403);
    }
    
    return $next($request);
}
```

### Blade Directives

```blade
@mobile
    <p>Mobile view content</p>
@endmobile

@desktop
    <p>Desktop view content</p>
@enddesktop

@tablet
    <p>Tablet view content</p>
@endtablet

@robot
    <p>Bot detected</p>
@endrobot

@tor
    <p>Tor browser detected</p>
@endtor
```

## Detected Browsers

- Google Chrome
- Mozilla Firefox
- Safari
- Microsoft Edge
- Opera / Opera GX
- Brave
- Vivaldi
- Tor Browser
- Kahf Browser
- Samsung Internet
- UC Browser
- Internet Explorer
- And many more...

## Detected Mobile Brands

- Apple (iPhone, iPad)
- Samsung
- Xiaomi
- Huawei
- OnePlus
- Oppo
- Vivo
- Google (Pixel)
- Motorola
- Nokia
- LG
- Sony
- And more...

## Detected Robots/Bots

- Googlebot
- Bingbot
- Yahoo Slurp
- DuckDuckBot
- Baiduspider
- YandexBot
- Facebook Bot
- Twitter Bot
- LinkedIn Bot
- And many more...

## Configuration

Edit `config/device-detector.php` or set via `.env`:

```php
return [
    // Enable/disable Tor detection
    'enable_tor_detection' => env('DEVICE_DETECTOR_TOR_DETECTION', true),

    // Cache duration for Tor exit nodes (seconds)
    'tor_cache_duration' => env('DEVICE_DETECTOR_TOR_CACHE', 3600),

    // Tor exit nodes source URL
    'tor_exit_node_url' => env('DEVICE_DETECTOR_TOR_URL', 'https://check.torproject.org/exit-addresses'),

    // Enable/disable robot detection
    'enable_robot_detection' => env('DEVICE_DETECTOR_ROBOT_DETECTION', true),

    // IP Geolocation via ipgeolocation.io (free plan: 30,000 req/month)
    'enable_ip_geolocation' => env('DEVICE_DETECTOR_GEO_ENABLED', false),
    'ip_geolocation_api_key' => env('DEVICE_DETECTOR_GEO_API_KEY', ''),
    'ip_geolocation_api_url' => env('DEVICE_DETECTOR_GEO_URL', 'https://api.ipgeolocation.io/v3/ipgeo'),
    'ip_geolocation_cache_duration' => env('DEVICE_DETECTOR_GEO_CACHE', 3600),
];
```

### Enabling IP Geolocation

1. Sign up at [ipgeolocation.io](https://ipgeolocation.io) and copy your **free API key** (30,000 requests/month — no credit card required)
2. Add to your application `.env` file (**never commit your API key to git**):

```env
DEVICE_DETECTOR_GEO_ENABLED=true
DEVICE_DETECTOR_GEO_API_KEY=your_own_api_key_here
```

> **Note:** Your `.env` file is already excluded from git by Laravel's default `.gitignore`. Never hardcode your API key in PHP files.

3. Use in your code:

```php
// Full detect — includes location
$data = DeviceDetector::detect();
$location = $data['location'];

echo $location['country'];      // e.g. Bangladesh
echo $location['country_code']; // e.g. BD
echo $location['city'];         // e.g. Dhaka
echo $location['state'];        // e.g. Dhaka Division
echo $location['timezone'];     // e.g. Asia/Dhaka
echo $location['isp'];          // e.g. Ranks ITT
echo $location['currency'];     // e.g. BDT
echo $location['calling_code']; // e.g. +880
$location['is_eu'];             // true/false

// Or use the dedicated helper:
$location = DeviceDetector::getLocation();
```

#### Geolocation Response Fields

| Field | Description | Example |
|-------|-------------|---------|
| `country` | Full country name | `Bangladesh` |
| `country_code` | ISO 2-letter code | `BD` |
| `city` | City name | `Dhaka` |
| `state` | State/province | `Dhaka Division` |
| `district` | District | `Dhaka` |
| `zip` | Postal/ZIP code | `1000` |
| `latitude` | Latitude | `23.72305` |
| `longitude` | Longitude | `90.40860` |
| `timezone` | Timezone name | `Asia/Dhaka` |
| `isp` | Internet Service Provider | `Ranks ITT` |
| `organization` | AS organization | `AS24323 Ranks ITT` |
| `currency` | Currency code | `BDT` |
| `calling_code` | Phone country code | `+880` |
| `is_eu` | EU member country | `false` |

## API Routes

The package provides test routes (disabled in production):

```
GET /device-detector/test
```

Returns JSON with detected device information.

## Requirements

| PHP Version | Supported |
|-------------|-----------|
| 8.1         | ✅ Yes    |
| 8.2         | ✅ Yes    |
| 8.3         | ✅ Yes    |
| 8.4         | ✅ Yes    |
| 8.5+        | ✅ Yes (future-compatible via `^8.1`) |

| Laravel Version | Supported |
|-----------------|-----------|
| 10.x            | ✅ Yes    |
| 11.x            | ✅ Yes    |
| 12.x            | ✅ Yes    |

The package uses `"php": "^8.1"` in composer.json, which means PHP 8.1 and all future 8.x releases (8.2, 8.3, 8.4, 8.5, etc.) are automatically supported without needing to update the package.

## Testing

```bash
composer test
```

## Security

If you discover any security issues, please email bestcyberking@gmail.com
 instead of using the issue tracker.

## Credits

- [Syed Sajid Akram](https://github.com/sajidwarner) - Package Author & Maintainer
- [Claude.ai](https://claude.ai) - AI Assistant by Anthropic (Package Development & Architecture)
- [ChatGPT](https://chat.openai.com/) - AI Assistant by OpenAI (Code Assistance & Suggestions)
- [Google Gemini](https://gemini.google/) - AI Assistant by Google (Code Assistance & Suggestions)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please open an issue on [GitHub](https://github.com/sajidwarner/laravel-device-detector).