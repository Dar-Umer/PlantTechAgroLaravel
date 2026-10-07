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

        return [
            'level' => $level,
            'messages' => $messages,
            'seasonal_stage' => static::seasonalStage($level === 'good'),
        ];
    }

    /**
     * Kashmir Apple Horticultural Phenological Calendar.
     * Maps real month & day to current phenological growth stage and guidance.
     *
     * @return array{stage: string, short: string, advisory: string, spray_window: string}
     */
    public static function seasonalStage(bool $favorableWeather = true): array
    {
        $month = (int) now()->format('n');
        $day = (int) now()->format('j');

        if ($month === 12 || $month === 1 || ($month === 2 && $day < 20)) {
            $stage = 'Dormant Stage';
            $short = 'Dormant';
            $advisory = 'Winter dormancy period. Focus on training, pruning and sanitizing fallen leaves.';
            $spray = $favorableWeather ? 'Dormant Oil Spray' : 'Postpone Spray (Cold/Frost)';
        } elseif (($month === 2 && $day >= 20) || ($month === 3 && $day <= 15)) {
            $stage = 'Silver Tip Stage';
            $short = 'Silver Tip';
            $advisory = 'Buds swelling. Optimal window for Copper Oxychloride preventive spray.';
            $spray = $favorableWeather ? 'Optimal Spray Window' : 'Postpone Spray (Weather)';
        } elseif ($month === 3 && $day > 15) {
            $stage = 'Green Tip Stage';
            $short = 'Green Tip';
            $advisory = 'Green tissue exposed. Monitor for scab ascospores release and apply protective cover.';
            $spray = $favorableWeather ? 'Fungicide Window Open' : 'Postpone Spray (Rain/Wind)';
        } elseif ($month === 4 && $day <= 15) {
            $stage = 'Pink Bud Stage';
            $short = 'Pink Bud';
            $advisory = 'Tight cluster / Pink bud. Protect blossom buds against scab and powdery mildew.';
            $spray = $favorableWeather ? 'Pre-Pink Cover Active' : 'Postpone Spray (Rain)';
        } elseif ($month === 4 && $day > 15) {
            $stage = 'Bloom Stage';
            $short = 'Bloom';
            $advisory = 'Full blossom. Strictly avoid insecticide sprays to safeguard pollinators and honeybees.';
            $spray = 'Pollination Safe Window';
        } elseif ($month === 5 && $day <= 15) {
            $stage = 'Petal Fall Stage';
            $short = 'Petal Fall';
            $advisory = '75% petals dropped. High susceptibility to primary scab infection; apply protective cover.';
            $spray = $favorableWeather ? 'Post-Bloom Cover' : 'Postpone Spray (Rain)';
        } elseif ($month === 5 || $month === 6) {
            $stage = 'Fruit Set / Walnut Stage';
            $short = 'Fruit Set';
            $advisory = 'Rapid cell division and fruit sizing. Maintain drip irrigation and apply calcium supplements.';
            $spray = $favorableWeather ? 'Nutrition & Cover' : 'Postpone Spray';
        } elseif ($month === 7 || $month === 8) {
            $stage = 'Fruit Sizing Stage';
            $short = 'Fruit Sizing';
            $advisory = 'Fruit development and color initiation. Monitor red mite populations and canopy sunlight.';
            $spray = $favorableWeather ? 'Cover Spray Active' : 'Postpone Spray';
        } elseif ($month === 9 || $month === 10) {
            $stage = 'Harvest / Post-Harvest';
            $short = 'Harvest';
            $advisory = 'Peak harvest and grading season. Prepare for post-harvest nutrition (Urea & Zinc sprays).';
            $spray = $favorableWeather ? 'Post-Harvest Nutrition' : 'Postpone Spray';
        } else {
            $stage = 'Leaf Fall Stage';
            $short = 'Leaf Fall';
            $advisory = 'Pre-dormancy preparation. Apply 5% urea spray on canopy to accelerate leaf decomposition.';
            $spray = $favorableWeather ? 'Leaf Sanitation Spray' : 'Postpone Spray';
        }

        return [
            'stage' => $stage,
            'short' => $short,
            'advisory' => $advisory,
            'spray_window' => $spray,
        ];
    }
}
