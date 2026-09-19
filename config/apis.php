<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third-Party API Integrations
    |--------------------------------------------------------------------------
    |
    | Managed from Admin → Settings → APIs. Values here are defaults;
    | anything saved in the admin panel (settings table as `apis.*`)
    | overrides them at runtime. Add future integrations (SMS gateway,
    | maps keys, …) as new cards in the APIs tab.
    |
    */

    // Google reCAPTCHA v3 (invisible, score-based).
    'recaptcha_enabled' => false,
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => '',
    'recaptcha_min_score' => 0.5,
];
