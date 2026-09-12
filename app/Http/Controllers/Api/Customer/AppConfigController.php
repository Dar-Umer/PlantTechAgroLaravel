<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Support\AppConfig;

class AppConfigController extends Controller
{
    public function show()
    {
        return response()->json([
            'app_config' => AppConfig::toArray(),
        ]);
    }
}