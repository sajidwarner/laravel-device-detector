<?php

namespace SajidWarner\DeviceDetector\Tests\Feature;

use Illuminate\Http\Request;
use SajidWarner\DeviceDetector\Facades\DeviceDetector;
use SajidWarner\DeviceDetector\Tests\TestCase;

class DeviceDetectorTest extends TestCase
{
    /** @test */
    public function it_can_detect_chrome_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Google Chrome', $result['browser']);
        $this->assertTrue($result['is_desktop']);
        $this->assertFalse($result['is_mobile']);
    }

    /** @test */
    public function it_can_detect_firefox_browser(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Firefox', $result['browser']);
        $this->assertEquals('Windows 10', $result['platform']);
    }

    /** @test */
    public function it_can_detect_mobile_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_mobile']);
        $this->assertFalse($result['is_desktop']);
        $this->assertEquals('Apple', $result['device_brand']);
        $this->assertEquals('mobile', $result['device_type']);
    }

    /** @test */
    public function it_can_detect_samsung_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Samsung', $result['device_brand']);
        $this->assertTrue($result['is_mobile']);
        $this->assertEquals('Android', $result['platform']);
    }

    /** @test */
    public function it_can_detect_tablet(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_tablet']);
        $this->assertFalse($result['is_mobile']);
        $this->assertEquals('tablet', $result['device_type']);
    }

    /** @test */
    public function it_can_detect_googlebot(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $result = DeviceDetector::detect($request);

        $this->assertTrue($result['is_robot']);
        $this->assertEquals('Googlebot', $result['robot_name']);
    }

    /** @test */
    public function it_can_use_facade_methods(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $this->assertFalse(DeviceDetector::isMobile($request));
        $this->assertTrue(DeviceDetector::isDesktop($request));
        $this->assertFalse(DeviceDetector::isRobot($request));
        $this->assertEquals('Google Chrome', DeviceDetector::getBrowser($request));
    }

    /** @test */
    public function it_detects_unknown_for_invalid_user_agent(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', '');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Unknown', $result['browser']);
    }

    /** @test */
    public function it_can_detect_xiaomi_device(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 13; Redmi Note 12 Pro) AppleWebKit/537.36');

        $result = DeviceDetector::detect($request);

        $this->assertEquals('Xiaomi', $result['device_brand']);
        $this->assertStringContainsString('Redmi', $result['device_model']);
    }
}
