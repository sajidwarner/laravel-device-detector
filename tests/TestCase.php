<?php

namespace SajidWarner\DeviceDetector\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SajidWarner\DeviceDetector\DeviceDetectorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DeviceDetectorServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'DeviceDetector' => \SajidWarner\DeviceDetector\Facades\DeviceDetector::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default configuration
        $app['config']->set('device-detector.enable_tor_detection', false);
        $app['config']->set('device-detector.enable_robot_detection', true);
    }
}
