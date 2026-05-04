<?php

namespace SajidWarner\LaraTrack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array  detect(\Illuminate\Http\Request $request = null)
 * @method static string getBrowser(\Illuminate\Http\Request $request = null)
 * @method static string getPlatform(\Illuminate\Http\Request $request = null)
 * @method static string getDeviceType(\Illuminate\Http\Request $request = null)
 * @method static bool   isMobile(\Illuminate\Http\Request $request = null)
 * @method static bool   isTablet(\Illuminate\Http\Request $request = null)
 * @method static bool   isDesktop(\Illuminate\Http\Request $request = null)
 * @method static bool   isRobot(\Illuminate\Http\Request $request = null)
 * @method static bool   isTor(\Illuminate\Http\Request $request = null)
 * @method static bool   isVpn(\Illuminate\Http\Request $request = null)
 * @method static bool   isProxy(\Illuminate\Http\Request $request = null)
 * @method static string getLanguage(\Illuminate\Http\Request $request = null)
 * @method static array  getLocation(\Illuminate\Http\Request $request = null)
 *
 * @see \SajidWarner\LaraTrack\LaraTrack
 */
class LaraTrack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laratrack';
    }
}
