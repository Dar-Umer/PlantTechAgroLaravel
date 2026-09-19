<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WeatherController extends Controller
{
    public function show(Request $request)
    {
        if (! (bool) config('weather.enabled', true)
            || ! (bool) config('weather.api_endpoint_enabled', true)) {
            return response()->json(['message' => 'Weather service is disabled.'], 404);
        }

        $data = $request->validate([
            'area' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', Rule::in(array_keys(WeatherService::districts()))],
        ]);

        $area = $data['area'] ?? $request->user()->area;

        if (! empty($data['district'])) {
            $districts = WeatherService::districts();
            $area = $districts[$data['district']]['label'];
        }

        $weather = WeatherService::forArea($area);

        if ($weather === null) {
            return response()->json(['message' => 'Weather is currently unavailable.'], 503);
        }

        return response()->json(['weather' => $weather]);
    }
}
