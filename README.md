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
    'browser' => 'Google Chrome',
    'browser_version' => '120.0',
    'platform' => 'Windows 10',
    'device_type' => 'desktop',
    'device_brand' => null,
    'device_model' => null,
    'is_mobile' => false,
    'is_tablet' => false,
    'is_desktop' => true,
    'is_robot' => false,
    'is_tor' => false,
    'robot_name' => null,
    'ip' => '192.168.1.1'
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

Edit `config/device-detector.php`:

```php
return [
    // Cache duration for Tor exit nodes (in seconds)
    'tor_cache_duration' => 3600,
    
    // Tor exit nodes URL
    'tor_exit_node_url' => 'https://check.torproject.org/exit-addresses',
    
    // Enable/disable Tor detection
    'enable_tor_detection' => true,
    
    // Enable/disable robot detection
    'enable_robot_detection' => true,
];
```

## API Routes

The package provides test routes (disabled in production):

```
GET /device-detector/test
```

Returns JSON with detected device information.

## Requirements

- PHP >= 8.1
- Laravel >= 10.0

## Testing

```bash
composer test
```

## Security

If you discover any security issues, please email security@example.com instead of using the issue tracker.

## Credits

- [Sajid Warner](https://github.com/sajidwarner)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please open an issue on [GitHub](https://github.com/sajidwarner/laravel-device-detector).