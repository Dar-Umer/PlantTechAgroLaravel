<?php

namespace App\Providers;

use App\Services\ShopSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    protected function loadShopSettings(): void
    {
        try {
            app(ShopSettingsService::class)->mergeIntoConfig();
        } catch (\Throwable $e) {
            // Ignore DB/schema errors during early bootstrap (e.g. before migrations run).
        }
    }

    public function boot(): void
    {
        $this->loadShopSettings();

        RateLimiter::for('leads', function (Request $request) {
            return Limit::perMinute(5)->by('lead-form:'.$request->ip());
        });

        View::composer('*', function ($view) {
            $paletteName = config('shop.theme_palette', 'emerald');
            $palettes = config('theme.palettes', []);
            $palette = $palettes[$paletteName]['colors'] ?? $palettes['emerald']['colors'];

            $fontFamily = config('shop.font_family', 'Inter');
            $fonts = config('theme.fonts', []);
            $fontGoogleName = $fonts[$fontFamily] ?? $fonts['Inter'];

            $siteName = config('shop.site_name', config('app.name', 'PTA Admin'));
            $brandParts = explode(' ', trim($siteName), 2);

            $theme = [
                'palette' => $palette,
                'sidebar' => config('shop.sidebar_style', 'dark'),
                'font' => $fontFamily,
                'fontGoogle' => $fontGoogleName,
                'logo_url' => config('shop.logo_url', ''),
                'site_name' => $siteName,
                'brand_first' => $brandParts[0] ?? $siteName,
                'brand_rest' => $brandParts[1] ?? '',
            ];

            $view->with('theme', $theme);
        });
    }
}
