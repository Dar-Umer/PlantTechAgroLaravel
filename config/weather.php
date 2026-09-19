<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Farm Weather (Open-Meteo)
    |--------------------------------------------------------------------------
    |
    | Free Open-Meteo forecast API — no key required.
    | All values below can be overridden from Admin → Settings → Weather
    | (persisted in the `settings` table as `weather.*`).
    |
    */

    // Master switch. When false, no upstream calls are made and every
    // consumer receives null / hides its weather UI.
    'enabled' => true,

    // Show a live preview inside the Weather settings tab even when the
    // service is disabled for farmers.
    'admin_preview' => true,

    'base_url' => 'https://api.open-meteo.com/v1/forecast',

    // Fallback district when a farmer's area is blank or unknown.
    'default_district' => 'srinagar',

    // Kashmir district → coordinates. Editable from the Weather tab
    // (stored as weather.districts). Keys must stay URL-safe slugs.
    'districts' => [
        'srinagar' => ['label' => 'Srinagar', 'lat' => 34.0837, 'lon' => 74.7973],
        'pulwama' => ['label' => 'Pulwama', 'lat' => 33.8792, 'lon' => 74.8997],
        'anantnag' => ['label' => 'Anantnag', 'lat' => 33.7311, 'lon' => 75.1489],
        'baramulla' => ['label' => 'Baramulla', 'lat' => 34.2090, 'lon' => 74.3434],
        'budgam' => ['label' => 'Budgam', 'lat' => 34.0169, 'lon' => 74.7224],
        'kupwara' => ['label' => 'Kupwara', 'lat' => 34.5262, 'lon' => 74.2546],
        'shopian' => ['label' => 'Shopian', 'lat' => 33.7176, 'lon' => 74.8334],
        'kulgam' => ['label' => 'Kulgam', 'lat' => 33.6414, 'lon' => 75.0131],
        'bandipora' => ['label' => 'Bandipora', 'lat' => 34.4261, 'lon' => 74.6372],
        'ganderbal' => ['label' => 'Ganderbal', 'lat' => 34.2268, 'lon' => 74.7789],
    ],

    // Upstream fetching.
    'cache_ttl_minutes' => 60,
    'timeout_seconds' => 5,
    'retries' => 2,
    'units' => 'metric', // metric | imperial
    'timezone' => 'auto',

    // Forecast content.
    'include_current' => true,
    'forecast_days' => 7, // 0 | 3 | 7 | 16 (0 = current only)
    'include_hourly' => false, // next 24h temp + precip probability

    // Advisory engine.
    'advisory_enabled' => true,
    'frost_threshold_c' => 2,
    'spray_wind_kmh' => 20,
    'spray_rain_prob' => 50,
    'heat_threshold_c' => 30,

    // Per-surface exposure gates.
    'show_admin_card' => true,
    'show_api_dashboard' => true,
    'show_app_config' => true,
    'api_endpoint_enabled' => true,
];
