<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile Apps Management
    |--------------------------------------------------------------------------
    |
    | Controls how the customer mobile app presents itself and handles
    | updates. Values stored here can be overridden from the admin panel
    | under "Mobile Apps" (persisted in the `settings` table).
    |
    */

    // Branding shown inside the app. Blank = use the website store name.
    'app_name' => '',

    // Tagline shown on the mobile app splash screen.
    'splash_tagline' => 'Growing trust, one harvest at a time',

    // Color / typography. Blank = follow the website Appearance settings.
    'app_palette' => '',   // key from config('theme.palettes')
    'app_font_family' => '', // key from config('theme.fonts')
    'app_logo_url' => '',  // dedicated app icon/logo (absolute /storage path or URL)

    // Version control of the customer app.
    'version' => '1.0.0',          // display version shown in the app
    'build_number' => '1',
    'minimum_supported' => '1.0.0', // oldest app version allowed to keep running
    'force_update' => false,        // block usage until the customer updates
    'android_update_url' => '',     // Play Store listing
    'ios_update_url' => '',         // App Store listing
    'release_notes' => '',          // what's new in this release

    // General app behaviour.
    'maintenance_mode' => false,    // shows a maintenance screen inside the app
    'echo_otp' => false,            // SECURITY: dev helper — must stay false in production
];