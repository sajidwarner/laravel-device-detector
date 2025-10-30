<?php

namespace SajidWarner\DeviceDetector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SajidWarner\DeviceDetector\Facades\DeviceDetector;

class DeviceDetectorController extends Controller
{
    /**
     * Test endpoint to display device detection information
     */
    public function test(Request $request): JsonResponse
    {
        $data = DeviceDetector::detect($request);

        return response()->json([
            'success' => true,
            'data' => $data,
            'headers' => [
                'User-Agent' => $request->header('User-Agent'),
                'Sec-CH-UA' => $request->header('Sec-CH-UA'),
                'Sec-CH-UA-Platform' => $request->header('Sec-CH-UA-Platform'),
                'X-Real-IP' => $request->header('X-Real-IP'),
            ]
        ], 200);
    }
}
