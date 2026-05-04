<?php

namespace SajidWarner\DeviceDetector\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use SajidWarner\DeviceDetector\Facades\DeviceDetector;
use SajidWarner\DeviceDetector\Tests\TestCase;

class DeviceDetectorTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Browser Detection
    // -------------------------------------------------------------------------

    #[Test]
    public function it_can_detect_chrome_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Google Chrome', $result['browser']);
        $this->assertTrue($result['is_desktop']);
        $this->assertFalse($result['is_mobile']);
        $this->assertNotEmpty($result['browser_version']);
    }

    #[Test]
    public function it_can_detect_firefox_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Firefox', $result['browser']);
        $this->assertEquals('Windows 10', $result['platform']);
        $this->assertEquals('121.0', $result['browser_version']);
    }

    #[Test]
    public function it_can_detect_safari_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Safari', $result['browser']);
        $this->assertEquals('macOS', $result['platform']);
    }

    #[Test]
    public function it_can_detect_microsoft_edge(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Microsoft Edge', $result['browser']);
    }

    #[Test]
    public function it_can_detect_samsung_internet(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 SamsungBrowser/21.0 Chrome/110.0.5481.154 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Samsung Internet', $result['browser']);
    }

    #[Test]
    public function it_can_detect_opera_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/106.0.0.0');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Opera', $result['browser']);
    }

    #[Test]
    public function it_returns_unknown_for_empty_user_agent(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', '');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Unknown', $result['browser']);
        $this->assertEquals('', $result['browser_version']);
    }

    // -------------------------------------------------------------------------
    // Platform Detection
    // -------------------------------------------------------------------------

    #[Test]
    public function it_can_detect_windows_10(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Windows 10', $result['platform']);
    }

    #[Test]
    public function it_can_detect_macos_platform(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('macOS', $result['platform']);
    }

    #[Test]
    public function it_can_detect_linux_platform(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Linux', $result['platform']);
    }

    #[Test]
    public function it_can_detect_android_platform(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; SM-S918B) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Android', $result['platform']);
    }

    #[Test]
    public function it_can_detect_ios_platform(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('iOS (iPhone)', $result['platform']);
    }

    // -------------------------------------------------------------------------
    // Device Type Detection
    // -------------------------------------------------------------------------

    #[Test]
    public function it_can_detect_mobile_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_mobile']);
        $this->assertFalse($result['is_desktop']);
        $this->assertFalse($result['is_tablet']);
        $this->assertEquals('Apple', $result['device_brand']);
        $this->assertEquals('mobile', $result['device_type']);
    }

    #[Test]
    public function it_can_detect_tablet(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_tablet']);
        $this->assertFalse($result['is_mobile']);
        $this->assertFalse($result['is_desktop']);
        $this->assertEquals('tablet', $result['device_type']);
    }

    #[Test]
    public function it_can_detect_desktop(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_desktop']);
        $this->assertFalse($result['is_mobile']);
        $this->assertFalse($result['is_tablet']);
        $this->assertEquals('desktop', $result['device_type']);
    }

    // -------------------------------------------------------------------------
    // Mobile Brand Detection
    // -------------------------------------------------------------------------

    #[Test]
    public function it_can_detect_samsung_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Samsung', $result['device_brand']);
        $this->assertTrue($result['is_mobile']);
        $this->assertEquals('Android', $result['platform']);
    }

    #[Test]
    public function it_can_detect_xiaomi_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; Redmi Note 12 Pro) AppleWebKit/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Xiaomi', $result['device_brand']);
        $this->assertStringContainsString('Redmi', $result['device_model']);
    }

    #[Test]
    public function it_can_detect_huawei_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 10; Huawei P30 Pro) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Huawei', $result['device_brand']);
    }

    #[Test]
    public function it_can_detect_oneplus_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; OnePlus 11) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('OnePlus', $result['device_brand']);
    }

    #[Test]
    public function it_can_detect_google_pixel_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 14; Pixel 8 Pro) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Google', $result['device_brand']);
        $this->assertStringContainsString('Pixel', $result['device_model']);
    }

    #[Test]
    public function it_can_detect_motorola_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; moto g84 5g) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Motorola', $result['device_brand']);
    }

    // -------------------------------------------------------------------------
    // Robot/Bot Detection
    // -------------------------------------------------------------------------

    #[Test]
    public function it_can_detect_googlebot(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_robot']);
        $this->assertEquals('Googlebot', $result['robot_name']);
    }

    #[Test]
    public function it_can_detect_bingbot(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_robot']);
        $this->assertEquals('Bingbot', $result['robot_name']);
    }

    #[Test]
    public function it_can_detect_facebook_bot(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_robot']);
        $this->assertEquals('Facebookbot', $result['robot_name']);
    }

    #[Test]
    public function it_can_detect_twitter_bot(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Twitterbot/1.0');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_robot']);
        $this->assertEquals('Twitterbot', $result['robot_name']);
    }

    #[Test]
    public function it_returns_no_robot_for_normal_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertFalse($result['is_robot']);
        $this->assertNull($result['robot_name']);
    }

    // -------------------------------------------------------------------------
    // Facade Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_can_use_facade_methods(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $this->assertFalse(DeviceDetector::isMobile($request));
        $this->assertTrue(DeviceDetector::isDesktop($request));
        $this->assertFalse(DeviceDetector::isRobot($request));
        $this->assertEquals('Google Chrome', DeviceDetector::getBrowser($request));
        $this->assertEquals('desktop', DeviceDetector::getDeviceType($request));
        $this->assertEquals('Windows 10', DeviceDetector::getPlatform($request));
    }

    // -------------------------------------------------------------------------
    // IP Geolocation
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_empty_location_when_geolocation_disabled(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertIsArray($result['location']);
        $this->assertEmpty($result['location']);
    }

    #[Test]
    public function it_returns_empty_location_when_api_key_is_missing(): void
    {
        config(['device-detector.enable_ip_geolocation' => true]);
        config(['device-detector.ip_geolocation_api_key' => '']);

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEmpty($result['location']);
    }

    #[Test]
    public function it_returns_geolocation_data_when_enabled_with_api_key(): void
    {
        Http::fake([
            'api.ipgeolocation.io/*' => Http::response([
                'ip' => '8.8.8.8',
                'country_name' => 'United States',
                'country_code2' => 'US',
                'city' => 'Mountain View',
                'state_prov' => 'California',
                'district' => '',
                'zipcode' => '94043',
                'latitude' => '37.38605',
                'longitude' => '-122.08385',
                'time_zone' => ['name' => 'America/Los_Angeles'],
                'isp' => 'Google LLC',
                'organization' => 'AS15169 Google LLC',
                'currency' => ['code' => 'USD'],
                'calling_code' => '+1',
                'is_eu' => false,
            ], 200),
        ]);

        Cache::flush();

        config(['device-detector.enable_ip_geolocation' => true]);
        config(['device-detector.ip_geolocation_api_key' => 'test-api-key']);

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120.0.0.0 Safari/537.36');
        $request->headers->set('X-Real-IP', '8.8.8.8');

        $result = DeviceDetector::detect($request);
        $location = $result['location'];

        $this->assertNotEmpty($location);
        $this->assertEquals('United States', $location['country']);
        $this->assertEquals('US', $location['country_code']);
        $this->assertEquals('Mountain View', $location['city']);
        $this->assertEquals('California', $location['state']);
        $this->assertEquals('America/Los_Angeles', $location['timezone']);
        $this->assertEquals('Google LLC', $location['isp']);
        $this->assertEquals('USD', $location['currency']);
        $this->assertFalse($location['is_eu']);
    }

    #[Test]
    public function it_caches_geolocation_results(): void
    {
        Http::fake([
            'api.ipgeolocation.io/*' => Http::response([
                'country_name' => 'Bangladesh',
                'country_code2' => 'BD',
                'city' => 'Dhaka',
                'state_prov' => 'Dhaka Division',
                'district' => 'Dhaka',
                'zipcode' => '1000',
                'latitude' => '23.72305',
                'longitude' => '90.40860',
                'time_zone' => ['name' => 'Asia/Dhaka'],
                'isp' => 'Ranks ITT',
                'organization' => 'AS24323 Ranks ITT',
                'currency' => ['code' => 'BDT'],
                'calling_code' => '+880',
                'is_eu' => false,
            ], 200),
        ]);

        Cache::flush();

        config(['device-detector.enable_ip_geolocation' => true]);
        config(['device-detector.ip_geolocation_api_key' => 'test-api-key']);

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120.0.0.0');
        $request->headers->set('X-Real-IP', '91.128.103.196');

        // First call — hits the API
        $result1 = DeviceDetector::detect($request);

        // Second call — should read from cache, not call API again
        $result2 = DeviceDetector::getLocation($request);

        $this->assertEquals('Bangladesh', $result1['location']['country']);
        $this->assertEquals('Dhaka', $result1['location']['city']);
        $this->assertEquals('Asia/Dhaka', $result1['location']['timezone']);
        Http::assertSentCount(1);
    }

    #[Test]
    public function it_returns_empty_location_on_api_failure(): void
    {
        Http::fake([
            'api.ipgeolocation.io/*' => Http::response([], 500),
        ]);

        Cache::flush();

        config(['device-detector.enable_ip_geolocation' => true]);
        config(['device-detector.ip_geolocation_api_key' => 'test-api-key']);

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120.0.0.0');
        $request->headers->set('X-Real-IP', '1.2.3.4');

        $result = DeviceDetector::detect($request);

        $this->assertIsArray($result['location']);
        $this->assertEmpty($result['location']);
    }

    #[Test]
    public function get_location_facade_method_works(): void
    {
        Http::fake([
            'api.ipgeolocation.io/*' => Http::response([
                'country_name' => 'Germany',
                'country_code2' => 'DE',
                'city' => 'Berlin',
                'state_prov' => 'Berlin',
                'district' => '',
                'zipcode' => '10115',
                'latitude' => '52.5200',
                'longitude' => '13.4050',
                'time_zone' => ['name' => 'Europe/Berlin'],
                'isp' => 'Deutsche Telekom',
                'organization' => 'Deutsche Telekom AG',
                'currency' => ['code' => 'EUR'],
                'calling_code' => '+49',
                'is_eu' => true,
            ], 200),
        ]);

        Cache::flush();

        config(['device-detector.enable_ip_geolocation' => true]);
        config(['device-detector.ip_geolocation_api_key' => 'test-api-key']);

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120.0.0.0');
        $request->headers->set('X-Real-IP', '80.0.0.1');

        $location = DeviceDetector::getLocation($request);

        $this->assertEquals('Germany', $location['country']);
        $this->assertEquals('Berlin', $location['city']);
        $this->assertEquals('EUR', $location['currency']);
        $this->assertTrue($location['is_eu']);
    }
}
