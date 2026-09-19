<?php

namespace App\Services;

use App\Support\AgriAdvisory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Resolve free-text area to a known district (slug, label, coords).
     * Unknown / blank areas fall back to the configured default district.
     *
     * @return array{district: string, label: string, lat: float, lon: float}
     */
    public static function resolveDistrict(?string $area): array
    {
        $districts = static::districts();
        $default = (string) config('weather.default_district', 'srinagar');

        $slug = strtolower(trim((string) $area));
        $slug = preg_replace('/[^a-z]/', '', $slug) ?? '';

        foreach ($districts as $key => $district) {
            $normKey = preg_replace('/[^a-z]/', '', strtolower($key)) ?? '';
            $normLabel = preg_replace('/[^a-z]/', '', strtolower($district['label'] ?? '')) ?? '';

            if ($slug !== '' && ($slug === $normKey || $slug === $normLabel)) {
                return [
                    'district' => $key,
                    'label' => $district['label'],
                    'lat' => (float) $district['lat'],
                    'lon' => (float) $district['lon'],
                ];
            }
        }

        $fallback = $districts[$default] ?? reset($districts);

        return [
            'district' => array_search($fallback, $districts, true) ?: $default,
            'label' => $fallback['label'],
            'lat' => (float) $fallback['lat'],
            'lon' => (float) $fallback['lon'],
        ];
    }

    /**
     * @return array<string, array{label: string, lat: float, lon: float}>
     */
    public static function districts(): array
    {
        $districts = config('weather.districts', []);

        return is_array($districts) && $districts !== []
            ? $districts
            : config()->get('weather.districts', []);
    }

    /**
     * Full farmer-facing payload for an area, or null when the service is
     * disabled / the upstream fetch fails. Never throws.
     */
    public static function forArea(?string $area): ?array
    {
        if (! (bool) config('weather.enabled', true)) {
            return null;
        }

        try {
            $location = static::resolveDistrict($area);
            $forecast = static::fetch($location['lat'], $location['lon']);

            if ($forecast === null) {
                return null;
            }

            $payload = [
                'location' => $location,
                'fetched_at' => now()->toISOString(),
            ];

            if ((bool) config('weather.include_current', true)) {
                $payload['current'] = $forecast['current'] ?? null;
            }

            $payload['daily'] = $forecast['daily'] ?? [];

            if ((bool) config('weather.include_hourly', false) && isset($forecast['hourly'])) {
                $payload['hourly'] = $forecast['hourly'];
            }

            $payload['advisory'] = (bool) config('weather.advisory_enabled', true)
                ? AgriAdvisory::fromForecast($forecast)
                : null;

            return $payload;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Raw shaped forecast for coordinates. Null on any failure.
     *
     * @return array{current: ?array<string, mixed>, daily: list<array<string, mixed>>, hourly?: list<array<string, mixed>>}|null
     */
    public static function fetch(float $lat, float $lon): ?array
    {
        $ttl = max(15, (int) config('weather.cache_ttl_minutes', 60));
        $key = sprintf('weather:%.4f,%.4f', $lat, $lon);

        try {
            return Cache::remember($key, now()->addMinutes($ttl), function () use ($lat, $lon) {
                $units = config('weather.units', 'metric') === 'imperial'
                    ? ['temperature_unit' => 'fahrenheit', 'wind_speed_unit' => 'mph']
                    : ['temperature_unit' => 'celsius', 'wind_speed_unit' => 'kmh'];

                $params = array_merge([
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max,uv_index_max',
                    'timezone' => config('weather.timezone', 'auto'),
                    'forecast_days' => max(0, min(16, (int) config('weather.forecast_days', 7))),
                ], $units);

                if ((bool) config('weather.include_hourly', false)) {
                    $params['hourly'] = 'temperature_2m,precipitation_probability';
                    $params['forecast_hours'] = 24;
                }

                $response = Http::timeout((int) config('weather.timeout_seconds', 5))
                    ->retry((int) config('weather.retries', 2), 200)
                    ->get((string) config('weather.base_url', 'https://api.open-meteo.com/v1/forecast'), $params);

                if (! $response->successful()) {
                    return null;
                }

                return static::shape($response->json());
            });
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Shape the upstream response into a stable, mobile-friendly structure.
     */
    public static function shape(?array $json): ?array
    {
        if (! is_array($json)) {
            return null;
        }

        $current = $json['current'] ?? null;
        $shaped = [
            'current' => is_array($current) ? [
                'temp' => $current['temperature_2m'] ?? null,
                'feels_like' => $current['apparent_temperature'] ?? null,
                'humidity' => $current['relative_humidity_2m'] ?? null,
                'precipitation' => $current['precipitation'] ?? null,
                'wind' => $current['wind_speed_10m'] ?? null,
                'code' => $current['weather_code'] ?? null,
                'label' => static::label((int) ($current['weather_code'] ?? -1)),
                'time' => $current['time'] ?? null,
            ] : null,
            'daily' => [],
        ];

        $daily = $json['daily'] ?? [];
        $times = $daily['time'] ?? [];

        if (is_array($times)) {
            foreach ($times as $i => $date) {
                $shaped['daily'][] = [
                    'date' => $date,
                    'temp_max' => $daily['temperature_2m_max'][$i] ?? null,
                    'temp_min' => $daily['temperature_2m_min'][$i] ?? null,
                    'precip_sum' => $daily['precipitation_sum'][$i] ?? null,
                    'rain_prob_max' => $daily['precipitation_probability_max'][$i] ?? null,
                    'wind_max' => $daily['wind_speed_10m_max'][$i] ?? null,
                    'uv_max' => $daily['uv_index_max'][$i] ?? null,
                ];
            }
        }

        if (isset($json['hourly']['time']) && is_array($json['hourly']['time'])) {
            $hourly = [];
            foreach ($json['hourly']['time'] as $i => $time) {
                if (count($hourly) >= 24) {
                    break;
                }
                $hourly[] = [
                    'time' => $time,
                    'temp' => $json['hourly']['temperature_2m'][$i] ?? null,
                    'rain_prob' => $json['hourly']['precipitation_probability'][$i] ?? null,
                ];
            }
            $shaped['hourly'] = $hourly;
        }

        return $shaped;
    }

    public static function label(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            $code >= 1 && $code <= 3 => 'Partly cloudy',
            $code >= 45 && $code <= 48 => 'Fog',
            $code >= 51 && $code <= 57 => 'Drizzle',
            $code >= 61 && $code <= 67 => 'Rain',
            $code >= 71 && $code <= 77 => 'Snow',
            $code >= 80 && $code <= 82 => 'Rain showers',
            $code >= 85 && $code <= 86 => 'Snow showers',
            $code === 95 => 'Thunderstorm',
            $code >= 96 => 'Thunderstorm with hail',
            default => 'Unknown',
        };
    }
}
