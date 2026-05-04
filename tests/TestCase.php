<?php

namespace SajidWarner\LaraTrack\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SajidWarner\LaraTrack\LaraTrackServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaraTrackServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'LaraTrack' => \SajidWarner\LaraTrack\Facades\LaraTrack::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('laratrack.enable_tor_detection', false);
        $app['config']->set('laratrack.enable_robot_detection', true);
        $app['config']->set('laratrack.enable_ip_geolocation', false);
    }
}
