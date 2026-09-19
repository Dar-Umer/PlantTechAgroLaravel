<?php

namespace App\Support;

/**
 * Rule-based orchard guidance derived from an Open-Meteo forecast payload.
 * Indicative heuristics only — not a substitute for agronomist advice.
 */
class AgriAdvisory
{
    /**
     * @param  array{current?: array<string, mixed>, daily?: list<array<string, mixed>>}  $forecast
     * @return array{level: string, messages: list<string>}
     */
    public static function fromForecast(array $forecast): array
    {
        $frostThreshold = (float) config('weather.frost_threshold_c', 2);
        $sprayWind = (float) config('weather.spray_wind_kmh', 20);
        $sprayRainProb = (int) config('weather.spray_rain_prob', 50);
        $heatThreshold = (float) config('weather.heat_threshold_c', 30);

        $daily = $forecast['daily'] ?? [];
        $today = $daily[0] ?? [];

        $messages = [];
        $level = 'good';

        // Frost risk across the forecast window.
        $frostDays = [];
        foreach ($daily as $day) {
            if (isset($day['temp_min']) && (float) $day['temp_min'] < $frostThreshold) {
                $frostDays[] = ($day['date'] ?? 'upcoming') . ' (' . $day['temp_min'] . '°C)';
            }
        }

        if ($frostDays !== []) {
            $level = 'alert';
            $messages[] = 'Frost risk on ' . implode(', ', array_slice($frostDays, 0, 3)) . ' — protect young orchards overnight.';
        }

        // Spraying window from today's wind + rain probability.
        $windMax = isset($today['wind_max']) ? (float) $today['wind_max'] : null;
        $rainProb = isset($today['rain_prob_max']) ? (int) $today['rain_prob_max'] : null;

        if (($windMax !== null && $windMax > $sprayWind)
            || ($rainProb !== null && $rainProb > $sprayRainProb)) {
            if ($level !== 'alert') {
                $level = 'caution';
            }
            $messages[] = 'Poor spraying conditions today'
                . ($windMax !== null ? " (wind {$windMax} km/h)" : '')
                . ($rainProb !== null ? " (rain {$rainProb}%)" : '')
                . ' — postpone pesticide sprays.';
        } else {
            $messages[] = 'Good spraying window today — calm and mostly dry.';
        }

        // Irrigation hint.
        $precipSum = isset($today['precip_sum']) ? (float) $today['precip_sum'] : null;
        $tempMax = isset($today['temp_max']) ? (float) $today['temp_max'] : null;

        if ($precipSum !== null && $tempMax !== null && $precipSum < 1 && $tempMax > $heatThreshold) {
            $messages[] = "Hot and dry today (max {$tempMax}°C) — consider irrigation for high-density blocks.";
        }

        return ['level' => $level, 'messages' => $messages];
    }
}
