# LaraTrack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sajidwarner/laravel-device-detector.svg?style=flat-square)](https://packagist.org/packages/sajidwarner/laravel-device-detector)
[![Total Downloads](https://img.shields.io/packagist/dt/sajidwarner/laravel-device-detector.svg?style=flat-square)](https://packagist.org/packages/sajidwarner/laravel-device-detector)
[![PHP Version](https://img.shields.io/packagist/php-v/sajidwarner/laravel-device-detector.svg?style=flat-square)](https://packagist.org/packages/sajidwarner/laravel-device-detector)
[![License](https://img.shields.io/packagist/l/sajidwarner/laravel-device-detector.svg?style=flat-square)](LICENSE.md)
[![Tests](https://img.shields.io/badge/tests-36%20passing-brightgreen?style=flat-square)](#testing)

A powerful Laravel package for **browser detection**, **device detection**, **IP geolocation**, **VPN/Proxy detection**, **bot/robot detection**, **Tor detection**, **language detection**, and **platform detection** — with built-in middleware, Laravel events, and Artisan commands. Supports Laravel 10, 11, 12, and 13 with PHP 8.1+.

## Features

- 🌐 **Browser Detection** — Chrome, Firefox, Safari, Edge, Opera, Brave, Vivaldi, Tor Browser, Kahf, DuckDuckGo, Samsung Internet, UC Browser, and more
- 📱 **Mobile Device Detection** — Detect brand and model (Apple, Samsung, Xiaomi, Huawei, OnePlus, Oppo, Vivo, Google Pixel, Motorola, Nokia, LG, Sony, HTC)
- 💻 **Desktop Detection** — Identify desktop browsers and operating systems
- 📊 **Tablet Detection** — iPad, Android tablets, Kindle, and more
- 🤖 **Robot / Bot Detection** — 26+ bots: Googlebot, Bingbot, Facebook, Twitter, LinkedIn, WhatsApp, Telegram, Semrush, Ahrefs, and more
- 🔒 **Tor Detection** — Real-time Tor exit node detection with caching
- 🛡️ **VPN & Proxy Detection** — Detect VPN and proxy connections via geolocation security data
- 🗺️ **IP Geolocation** — Country, city, timezone, ISP, currency via [ipgeolocation.io](https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY) (free plan available)
- 🌍 **Language Detection** — Detect visitor's preferred language from `Accept-Language` header
- 🖥️ **Platform / OS Detection** — Windows 10, macOS, Linux, Android, iOS, Chrome OS, and more
- 🚧 **Built-in Middleware** — Block bots, Tor, VPN, specific countries, or restrict to mobile/desktop
- ⚡ **Laravel Events** — Fire events on bot, Tor, or VPN detection
- 🖥️ **Artisan Commands** — `laratrack:test`, `laratrack:clear-cache`
- 🎯 **Client Hints Support** — Uses modern `Sec-CH-UA` headers as primary detection method
- 🔧 **Blade Directives** — `@mobile`, `@tablet`, `@desktop`, `@robot`, `@tor`, `@vpn`, `@proxy`
- ⚡ **Performance** — Per-IP caching for geolocation and Tor nodes

## Installation

```bash
composer require sajidwarner/laravel-device-detector
```

Laravel's auto-discovery will automatically register the service provider and facade.

### Manual Registration (Optional)

If auto-discovery is disabled, add to `config/app.php`:

```php
'providers' => [
    SajidWarner\LaraTrack\LaraTrackServiceProvider::class,
],

'aliases' => [
    'LaraTrack' => SajidWarner\LaraTrack\Facades\LaraTrack::class,
],
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="SajidWarner\LaraTrack\LaraTrackServiceProvider"
```

## Usage

### Basic Usage

```php
use SajidWarner\LaraTrack\Facades\LaraTrack;

$browser   = LaraTrack::getBrowser();     // "Google Chrome"
$platform  = LaraTrack::getPlatform();    // "Windows 10"
$type      = LaraTrack::getDeviceType();  // "desktop" | "mobile" | "tablet"
$language  = LaraTrack::getLanguage();    // "en-US", "bn-BD" ...
$isMobile  = LaraTrack::isMobile();       // true / false
$isTablet  = LaraTrack::isTablet();       // true / false
$isDesktop = LaraTrack::isDesktop();      // true / false
$isRobot   = LaraTrack::isRobot();        // true / false
$isTor     = LaraTrack::isTor();          // true / false
$isVpn     = LaraTrack::isVpn();          // true / false
$isProxy   = LaraTrack::isProxy();        // true / false
$location  = LaraTrack::getLocation();    // array (when geolocation enabled)
```

### Full Detection Array

```php
$data = LaraTrack::detect();

/*
[
    'browser'         => 'Google Chrome',
    'browser_version' => '120.0',
    'platform'        => 'Windows 10',
    'device_type'     => 'desktop',
    'device_brand'    => null,
    'device_model'    => null,
    'is_mobile'       => false,
    'is_tablet'       => false,
    'is_desktop'      => true,
    'is_robot'        => false,
    'is_tor'          => false,
    'is_vpn'          => false,
    'is_proxy'        => false,
    'robot_name'      => null,
    'language'        => 'en-US',
    'ip'              => '192.168.1.1',
    'location'        => [                  // only when geolocation is enabled
        'country'       => 'Bangladesh',
        'country_code'  => 'BD',
        'city'          => 'Dhaka',
        'state'         => 'Dhaka Division',
        'district'      => 'Dhaka',
        'zip'           => '1000',
        'latitude'      => '23.72305',
        'longitude'     => '90.40860',
        'timezone'      => 'Asia/Dhaka',
        'isp'           => 'Ranks ITT',
        'organization'  => 'AS24323 Ranks ITT',
        'currency'      => 'BDT',
        'calling_code'  => '+880',
        'is_eu'         => false,
    ],
]
*/
```

### In Controllers

```php
use Illuminate\Http\Request;
use SajidWarner\LaraTrack\Facades\LaraTrack;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $device = LaraTrack::detect($request);

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

## Middleware

LaraTrack registers 6 middleware aliases automatically — no manual registration needed.

### Available Middleware

```php
// Block all bots/crawlers
Route::middleware('laratrack.block-bots')->group(function () {
    Route::get('/members', MembersController::class);
});

// Block Tor connections
Route::middleware('laratrack.block-tor')->group(function () {
    Route::post('/checkout', CheckoutController::class);
});

// Block VPN and Proxy connections
Route::middleware('laratrack.block-vpn')->group(function () {
    Route::post('/login', LoginController::class);
});

// Allow mobile devices only
Route::middleware('laratrack.mobile-only')->group(function () {
    Route::get('/app', AppController::class);
});

// Allow desktop only
Route::middleware('laratrack.desktop-only')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});

// Block specific countries (inline)
Route::middleware('laratrack.block-countries:CN,RU,KP')->group(function () {
    Route::get('/api', ApiController::class);
});
```

### Country Blocking via Config

Block countries globally in `config/laratrack.php`:

```php
'blocked_countries' => ['CN', 'RU', 'KP'],
```

Then just apply the middleware without parameters:

```php
Route::middleware('laratrack.block-countries')->group(...);
```

### Custom Middleware Messages / Redirects

```php
// config/laratrack.php
'middleware' => [
    'bot_message'          => 'Bots are not allowed.',
    'tor_message'          => 'Tor connections are not allowed.',
    'vpn_message'          => 'VPN/Proxy connections are not allowed.',
    'country_message'      => 'Your country is not allowed.',
    'mobile_only_message'  => 'Please use a mobile device.',
    'desktop_only_message' => 'Please use a desktop browser.',
    'mobile_redirect'      => '/download-app', // redirect desktop users
    'desktop_redirect'     => '/desktop-only', // redirect mobile users
],
```

## Events

LaraTrack fires Laravel events automatically when threats are detected. Listen to them in your `EventServiceProvider` or using `#[AsEventListener]`.

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \SajidWarner\LaraTrack\Events\BotDetected::class  => [App\Listeners\HandleBot::class],
    \SajidWarner\LaraTrack\Events\TorDetected::class  => [App\Listeners\HandleTor::class],
    \SajidWarner\LaraTrack\Events\VpnDetected::class  => [App\Listeners\HandleVpn::class],
];
```

### Event Payloads

```php
// BotDetected
$event->request;  // Illuminate\Http\Request
$event->botName;  // "Googlebot"
$event->ip;       // "66.249.66.1"

// TorDetected
$event->request;
$event->ip;

// VpnDetected
$event->request;
$event->ip;
$event->type;     // "vpn" or "proxy"
```

### Example Listener

```php
namespace App\Listeners;

use SajidWarner\LaraTrack\Events\BotDetected;
use Illuminate\Support\Facades\Log;

class HandleBot
{
    public function handle(BotDetected $event): void
    {
        Log::warning("Bot detected: {$event->botName} from IP {$event->ip}");
    }
}
```

Disable events in config if not needed:

```php
'fire_events' => false,
```

## Artisan Commands

### Test Detection

Test LaraTrack detection from the terminal with any User-Agent string:

```bash
# Default Chrome UA
php artisan laratrack:test

# Custom User-Agent
php artisan laratrack:test "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15"

# With IP (for geolocation)
php artisan laratrack:test "Mozilla/5.0 Chrome/120.0.0.0" --ip=8.8.8.8
```

Example output:
```
LaraTrack Detection Result
─────────────────────────────────
+──────────────+──────────────────────+
| Field        | Value                |
+──────────────+──────────────────────+
| Browser      | Google Chrome 120.0  |
| Platform     | Windows 10           |
| Device Type  | desktop              |
| Is Mobile    | No                   |
| Is Robot     | No                   |
| Is Tor       | No                   |
| Is VPN       | No                   |
| Language     | en-US                |
| IP           | 8.8.8.8              |
+──────────────+──────────────────────+
```

### Clear Cache

```bash
php artisan laratrack:clear-cache
```

Clears cached Tor exit nodes and all geolocation results.

## Blade Directives

```blade
@mobile
    <p>Shown only on mobile</p>
@endmobile

@tablet
    <p>Shown only on tablets</p>
@endtablet

@desktop
    <p>Shown only on desktop</p>
@enddesktop

@robot
    <p>Bot/crawler detected</p>
@endrobot

@tor
    <p>Tor browser detected</p>
@endtor

@vpn
    <p>VPN connection detected</p>
@endvpn

@proxy
    <p>Proxy connection detected</p>
@endproxy
```

## IP Geolocation

Get real-time location data for any visitor IP using [ipgeolocation.io](https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY).

**Free plan:** 30,000 requests/month — no credit card required.

> 🔗 **Sign up here (free):** [https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY](https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY)

### Setup

1. Sign up at [ipgeolocation.io](https://app.ipgeolocation.io/signup?referral=AFF-YWEVCOJFNY) and get your free API key
2. Add to your `.env` file (**never commit your API key to git**):

```env
LARATRACK_GEO_ENABLED=true
LARATRACK_GEO_API_KEY=your_own_api_key_here
```

3. Use in your application:

```php
$location = LaraTrack::getLocation();

echo $location['country'];      // Bangladesh
echo $location['city'];         // Dhaka
echo $location['timezone'];     // Asia/Dhaka
echo $location['isp'];          // Ranks ITT
echo $location['currency'];     // BDT
echo $location['calling_code']; // +880
```

### Geolocation Response Fields

| Field | Description | Example |
|-------|-------------|---------|
| `country` | Full country name | `Bangladesh` |
| `country_code` | ISO 2-letter code | `BD` |
| `city` | City name | `Dhaka` |
| `state` | State / Province | `Dhaka Division` |
| `district` | District | `Dhaka` |
| `zip` | Postal / ZIP code | `1000` |
| `latitude` | Latitude | `23.72305` |
| `longitude` | Longitude | `90.40860` |
| `timezone` | Timezone name | `Asia/Dhaka` |
| `isp` | Internet Service Provider | `Ranks ITT` |
| `organization` | AS organization | `AS24323 Ranks ITT` |
| `currency` | Currency code | `BDT` |
| `calling_code` | Phone country code | `+880` |
| `is_eu` | EU member country | `false` |

## Detected Browsers

| Browser | Detection Method |
|---------|-----------------|
| Google Chrome | User-Agent + Sec-CH-UA |
| Mozilla Firefox | User-Agent |
| Safari | User-Agent |
| Microsoft Edge | User-Agent + Sec-CH-UA |
| Opera / Opera GX | User-Agent |
| Brave | User-Agent + Sec-CH-UA |
| Vivaldi | User-Agent |
| Tor Browser | User-Agent |
| Kahf Browser | X-Requested-With header |
| DuckDuckGo Browser | X-Requested-With + User-Agent |
| Samsung Internet | User-Agent |
| UC Browser | User-Agent |
| Internet Explorer | User-Agent |
| Chromium | User-Agent |

## Detected Mobile Brands & Models

| Brand | Models Detected |
|-------|----------------|
| Apple | iPhone, iPad (with model number) |
| Samsung | Galaxy series, SM- model codes |
| Xiaomi | Redmi, Mi, Poco series |
| Huawei | Huawei, Honor series |
| OnePlus | All OnePlus models |
| Oppo | All Oppo models |
| Vivo | All Vivo models |
| Google | Pixel series |
| Motorola | Moto series |
| Nokia | All Nokia models |
| LG | All LG models |
| Sony | All Sony models |
| HTC | All HTC models |

## Detected Robots & Bots

Googlebot, Bingbot, Yahoo Slurp, DuckDuckBot, Baiduspider, YandexBot, Sogou, Exabot, facebot, ia_archiver, Facebookbot, Twitterbot, LinkedInBot, WhatsApp, Telegram, Discordbot, Slackbot, Applebot, AhrefsBot, SemrushBot, MJ12bot, DotBot, Screaming Frog, SEOkicks, and more.

## Detected Platforms / Operating Systems

Windows 10, Windows 8.1, Windows 8, Windows 7, Windows Vista, Windows XP, macOS, iOS (iPhone), iOS (iPad), iPadOS, Android, Ubuntu, Linux, Chrome OS, BlackBerry, Windows Phone.

## Configuration

Full `config/laratrack.php`:

```php
return [
    // Tor Detection
    'enable_tor_detection' => env('LARATRACK_TOR_DETECTION', true),
    'tor_cache_duration'   => env('LARATRACK_TOR_CACHE', 3600),
    'tor_exit_node_url'    => env('LARATRACK_TOR_URL', 'https://check.torproject.org/exit-addresses'),

    // Robot Detection
    'enable_robot_detection' => env('LARATRACK_ROBOT_DETECTION', true),

    // IP Geolocation
    'enable_ip_geolocation'         => env('LARATRACK_GEO_ENABLED', false),
    'ip_geolocation_api_key'        => env('LARATRACK_GEO_API_KEY', ''),
    'ip_geolocation_api_url'        => env('LARATRACK_GEO_URL', 'https://api.ipgeolocation.io/v3/ipgeo'),
    'ip_geolocation_cache_duration' => env('LARATRACK_GEO_CACHE', 3600),

    // Events
    'fire_events' => env('LARATRACK_FIRE_EVENTS', true),

    // Country Blocking
    'blocked_countries' => [],

    // Middleware Messages & Redirects
    'middleware' => [
        'bot_message'          => 'Access denied: bots are not allowed.',
        'tor_message'          => 'Access denied: Tor connections are not allowed.',
        'vpn_message'          => 'Access denied: VPN/Proxy connections are not allowed.',
        'country_message'      => 'Access denied: your country is not allowed.',
        'mobile_only_message'  => 'This page is only available on mobile devices.',
        'desktop_only_message' => 'This page is only available on desktop.',
        'mobile_redirect'      => null,
        'desktop_redirect'     => null,
    ],
];
```

## Requirements

| PHP Version | Supported |
|-------------|-----------|
| 8.1 | ✅ |
| 8.2 | ✅ |
| 8.3 | ✅ |
| 8.4 | ✅ |
| 8.5+ | ✅ (future-compatible via `^8.1`) |

| Laravel Version | Supported |
|-----------------|-----------|
| 10.x | ✅ |
| 11.x | ✅ |
| 12.x | ✅ |
| 13.x | ✅ |

## Testing

```bash
composer test
```

36 tests · 86 assertions · all passing ✅

## API Routes (Non-Production Only)

```
GET /laratrack/test
```

Returns JSON with full detection data including headers. Disabled automatically in production.

## Security

If you discover any security issues, please email [bestcyberking@gmail.com](mailto:bestcyberking@gmail.com) instead of using the issue tracker.

## Credits

- [Syed Sajid Akram](https://github.com/sajidwarner) — Package Author & Maintainer
- [Claude.ai](https://claude.ai) — AI Assistant by Anthropic
- [ChatGPT](https://chat.openai.com/) — AI Assistant by OpenAI
- [Google Gemini](https://gemini.google/) — AI Assistant by Google

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please open an issue on [GitHub](https://github.com/sajidwarner/laratrack).

---

**Keywords:** laratrack, laravel device detector, laravel browser detection, laravel user agent parser, laravel mobile detection, laravel ip geolocation, laravel bot detection, laravel tor detection, laravel vpn detection, laravel middleware, php device detection, laravel package
