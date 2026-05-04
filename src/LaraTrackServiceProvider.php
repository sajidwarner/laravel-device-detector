<?php

namespace SajidWarner\LaraTrack;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('mobile',  fn () => app('laratrack')->isMobile());
        Blade::if('tablet',  fn () => app('laratrack')->isTablet());
        Blade::if('desktop', fn () => app('laratrack')->isDesktop());
        Blade::if('robot',   fn () => app('laratrack')->isRobot());
        Blade::if('tor',     fn () => app('laratrack')->isTor());
    }
}
