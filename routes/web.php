<?php

use Illuminate\Support\Facades\Route;
use SajidWarner\DeviceDetector\Http\Controllers\DeviceDetectorController;

Route::get('/device-detector/test', [DeviceDetectorController::class, 'test'])
    ->name('device-detector.test');
