<?php

use Illuminate\Support\Facades\Route;
use SajidWarner\LaraTrack\Http\Controllers\LaraTrackController;

Route::get('/laratrack/test', [LaraTrackController::class, 'test'])
    ->name('laratrack.test');
