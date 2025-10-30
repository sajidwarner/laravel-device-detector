<?php

namespace SajidWarner\DeviceDetector\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array detect(\Illuminate\Http\Request $request = null)
 * @method static string getBrowser(\Illuminate\Http\Request $request = null)
 * @method static string getPlatform(\Illuminate\Http\Request $request = null)
 * @method static string getDeviceType(\Illuminate\Http\Request $request = null)
 * @method static bool isMobile(\Illuminate\Http\Request $request = null)
 * @method static bool isTablet(\Illuminate\Http\Request $request = null)
 * @method static bool isDesktop(\Illuminate\Http\Request $request = null)
 * @method static bool isRobot(\Illuminate\Http\Request $request = null)
 * @method static bool isTor(\Illuminate\Http\Request $request = null)
 *
 * @see \SajidWarner\DeviceDetector\DeviceDetector
 */
class DeviceDetector extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'device-detector';
    }
}
