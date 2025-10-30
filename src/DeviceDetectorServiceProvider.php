<?php

namespace SajidWarner\DeviceDetector;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class DeviceDetectorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/device-detector.php',
            'device-detector'
        );

        $this->app->singleton('device-detector', function ($app) {
            return new DeviceDetector($app['request']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/device-detector.php' => config_path('device-detector.php'),
        ], 'device-detector-config');

        // Register routes
        if (!$this->app->environment('production')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        // Register Blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register Blade directives for device detection
     */
    protected function registerBladeDirectives(): void
    {
        Blade::if('mobile', function () {
            return app('device-detector')->isMobile();
        });

        Blade::if('tablet', function () {
            return app('device-detector')->isTablet();
        });

        Blade::if('desktop', function () {
            return app('device-detector')->isDesktop();
        });

        Blade::if('robot', function () {
            return app('device-detector')->isRobot();
        });

        Blade::if('tor', function () {
            return app('device-detector')->isTor();
        });
    }
}
