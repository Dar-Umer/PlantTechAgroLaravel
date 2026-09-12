<?php

namespace App\Providers;

use App\Services\MailSettingsService;
use App\Services\ShopSettingsService;
use App\Support\Phone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

    protected function applyMailSettings(): void
    {
        try {
            app(MailSettingsService::class)->applyFromStored();
        } catch (\Throwable $e) {
            // Ignore DB/schema errors during early bootstrap (e.g. before migrations run).
        }
    }

    public function boot(): void
    {
        $this->loadShopSettings();
        $this->applyMailSettings();

        RateLimiter::for('leads', function (Request $request) {
            return Limit::perMinute(5)->by('lead-form:'.$request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            $key = Str::lower($request->input('email', '')).'|'.$request->ip();

            return Limit::perMinute(5)->by('admin-login:'.$key)->response(function () {
                return back()->withErrors([
                    'email' => 'Too many login attempts. Please wait a minute before trying again.',
                ]);
            });
        });

        RateLimiter::for('customer-login', function (Request $request) {
            $phone = Phone::digits((string) $request->input('phone', ''));

            return Limit::perMinute(5)->by('customer-login:'.$phone.'|'.$request->ip())->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please wait a minute before trying again.',
                ], 429);
            });
        });

        RateLimiter::for('customer-otp', function (Request $request) {
            $phone = Phone::digits((string) $request->input('phone', ''));

            return Limit::perMinute(3)->by('customer-otp:'.$phone.'|'.$request->ip())->response(function () {
                return response()->json([
                    'message' => 'Too many OTP requests. Please wait a minute before trying again.',
                ], 429);
            });
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
