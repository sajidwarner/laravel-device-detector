<?php

namespace SajidWarner\LaraTrack;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use SajidWarner\LaraTrack\Commands\ClearCache;
use SajidWarner\LaraTrack\Commands\TestDetection;
use SajidWarner\LaraTrack\Middleware\BlockBots;
use SajidWarner\LaraTrack\Middleware\BlockCountries;
use SajidWarner\LaraTrack\Middleware\BlockTor;
use SajidWarner\LaraTrack\Middleware\BlockVpn;
use SajidWarner\LaraTrack\Middleware\DesktopOnly;
use SajidWarner\LaraTrack\Middleware\MobileOnly;

class LaraTrackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laratrack.php', 'laratrack');

        $this->app->singleton('laratrack', function ($app) {
            return new LaraTrack($app['request']);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laratrack.php' => config_path('laratrack.php'),
        ], 'laratrack-config');

        if (!$this->app->environment('production')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        $this->registerBladeDirectives();
        $this->registerMiddlewareAliases();

        if ($this->app->runningInConsole()) {
            $this->commands([ClearCache::class, TestDetection::class]);
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('mobile',  fn () => app('laratrack')->isMobile());
        Blade::if('tablet',  fn () => app('laratrack')->isTablet());
        Blade::if('desktop', fn () => app('laratrack')->isDesktop());
        Blade::if('robot',   fn () => app('laratrack')->isRobot());
        Blade::if('tor',     fn () => app('laratrack')->isTor());
        Blade::if('vpn',     fn () => app('laratrack')->isVpn());
        Blade::if('proxy',   fn () => app('laratrack')->isProxy());
    }

    protected function registerMiddlewareAliases(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('laratrack.block-bots',      BlockBots::class);
        $router->aliasMiddleware('laratrack.block-tor',       BlockTor::class);
        $router->aliasMiddleware('laratrack.block-vpn',       BlockVpn::class);
        $router->aliasMiddleware('laratrack.mobile-only',     MobileOnly::class);
        $router->aliasMiddleware('laratrack.desktop-only',    DesktopOnly::class);
        $router->aliasMiddleware('laratrack.block-countries', BlockCountries::class);
    }
}
