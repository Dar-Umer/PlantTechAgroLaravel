<?php

namespace App\Providers;

use App\Models\GalleryImage;
use App\Models\HomeSection;
use App\Models\ImpactStat;
use App\Models\LeadFormField;
use App\Models\Partner;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceStage;
use App\Models\Testimonial;
use App\Services\MailSettingsService;
use App\Services\ShopSettingsService;
use App\Support\ContentCache;
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

    /**
     * Bust the cached front-end payload whenever content that feeds it changes.
     */
    protected function registerFrontendCacheInvalidation(): void
    {
        $models = [
            HomeSection::class,
            Service::class,
            ServiceItem::class,
            ServiceStage::class,
            Partner::class,
            GalleryImage::class,
            ImpactStat::class,
            Project::class,
            Post::class,
            PostCategory::class,
            Testimonial::class,
            LeadFormField::class,
            \App\Models\Variety::class,
        ];

        foreach ($models as $model) {
            $model::saved(fn () => ContentCache::bump());
            $model::deleted(fn () => ContentCache::bump());
        }
    }

    public function boot(): void
    {
        $this->loadShopSettings();
        $this->applyMailSettings();
        $this->registerFrontendCacheInvalidation();

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

        View::composer(['landing.layout', 'admin.layout', 'admin.auth.*'], function ($view) {
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

        // Farm weather for the admin top navbar. Cached per district upstream,
        // so this is a cheap cache hit on every admin page. Never throws and
        // never fires HTTP in the test environment.
        View::composer('admin.layout', function ($view) {
            $headerWeather = null;
            $headerWeatherDisabled = ! (bool) config('weather.enabled', true);

            if (! $headerWeatherDisabled
                && (bool) config('weather.show_admin_card', true)
                && ! app()->environment('testing')) {
                try {
                    $headerWeather = \App\Services\WeatherService::forArea(null);
                } catch (\Throwable) {
                    $headerWeather = null;
                }
            }

            $view->with('headerWeather', $headerWeather);
            $view->with('headerWeatherDisabled', $headerWeatherDisabled);
        });
    }
}
