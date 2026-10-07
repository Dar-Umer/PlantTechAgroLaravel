<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Support\AppConfig;

class AppConfigController extends Controller
{
    public function show()
    {
        $config = AppConfig::toArray();

        return response()->json(array_merge([
            'app_config' => $config,
        ], $config));
    }
}