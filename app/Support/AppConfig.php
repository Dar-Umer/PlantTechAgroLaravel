<?php

namespace App\Support;

class AppConfig
{
    public static function toArray(): array
    {
        // The mobile app can override the website branding. Blank = follow website.
        $paletteName = empty(config('mobile.app_palette'))
            ? config('shop.theme_palette', 'emerald')
            : config('mobile.app_palette');

        $palettes = config('theme.palettes', []);
        $palette = $palettes[$paletteName]['colors'] ?? $palettes['emerald']['colors'];

        $fontFamily = empty(config('mobile.app_font_family'))
            ? config('shop.font_family', 'Inter')
            : config('mobile.app_font_family');

        $logoUrl = ! empty(config('mobile.app_logo_url'))
            ? Media::url(config('mobile.app_logo_url'))
            : Media::url(config('shop.logo_url'));

        $siteName = config('shop.site_name', config('app.name', 'PTA Admin'));

        return [
            'app' => [
                'name' => $siteName,
                'env' => config('app.env'),
            ],
            'theme' => [
                'palette' => array_map('strval', $palette),
                'font_family' => $fontFamily,
                'logo_url' => $logoUrl,
            ],
            'mobile' => [
                'name' => ! empty(config('mobile.app_name')) ? config('mobile.app_name') : $siteName,
                'splash_tagline' => config('mobile.splash_tagline', ''),
                'version' => config('mobile.version', '1.0.0'),
                'build_number' => config('mobile.build_number', '1'),
                'minimum_supported' => config('mobile.minimum_supported', '1.0.0'),
                'force_update' => (bool) config('mobile.force_update', false),
                'maintenance_mode' => (bool) config('mobile.maintenance_mode', false),
                'android_update_url' => config('mobile.android_update_url', ''),
                'ios_update_url' => config('mobile.ios_update_url', ''),
                'release_notes' => config('mobile.release_notes', ''),
            ],
            'currency' => config('shop.currency', '₹'),
            'support' => [
                'phone' => config('shop.site_phone', ''),
                'email' => config('shop.site_email', ''),
                'address' => config('shop.site_address', ''),
                'hours' => config('shop.support_hours', ''),
            ],
            'company' => [
                'name' => config('invoice.company_name', $siteName),
                'address' => config('invoice.address', ''),
                'phone' => config('invoice.phone', ''),
                'email' => config('invoice.email', ''),
                'gst_no' => config('invoice.gst_no', ''),
                'terms' => config('invoice.terms', ''),
            ],
        ];
    }
}