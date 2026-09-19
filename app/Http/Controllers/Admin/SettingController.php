<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MailSettingsService;
use App\Services\ShopSettingsService;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        $palettes = config('theme.palettes', []);
        $fonts = config('theme.fonts', []);
        $sidebarStyles = config('theme.sidebar_styles', []);

        $settings = [
            'site_name' => config('shop.site_name', config('app.name', 'PTA Admin')),
            'site_email' => config('shop.site_email', 'admin@pta.com'),
            'site_phone' => config('shop.site_phone', '+91 98765 43210'),
            'site_address' => config('shop.site_address', ''),
            'support_hours' => config('shop.support_hours', 'Mon – Sat, 9 AM – 6 PM'),
            'footer_tagline' => config('shop.footer_tagline', 'Admin panel for PTA.'),
            'return_policy_text' => config('shop.return_policy_text', ''),
            'theme_palette' => config('shop.theme_palette', 'emerald'),
            'sidebar_style' => config('shop.sidebar_style', 'dark'),
            'font_family' => config('shop.font_family', 'Inter'),
            'logo_url' => config('shop.logo_url', ''),
            'favicon_url' => config('shop.favicon_url', ''),
            'social_facebook' => config('shop.social_facebook', ''),
            'social_instagram' => config('shop.social_instagram', ''),
            'social_youtube' => config('shop.social_youtube', ''),
            'social_whatsapp' => config('shop.social_whatsapp', ''),
            'social_x' => config('shop.social_x', ''),
        ];

        $seoSettings = [
            'meta_title' => config('seo.meta_title', config('shop.site_name', 'Plant Tech Agro')),
            'meta_description' => config('seo.meta_description', config('shop.seo_meta_description', '')),
            'meta_keywords' => config('seo.meta_keywords', config('shop.seo_meta_keywords', '')),
            'author' => config('seo.author', ''),
            'canonical_url' => config('seo.canonical_url', ''),
            'robots_index' => (bool) config('seo.robots_index', true),
            'robots_follow' => (bool) config('seo.robots_follow', true),
            'og_enabled' => (bool) config('seo.og_enabled', true),
            'og_type' => config('seo.og_type', 'website'),
            'og_site_name' => config('seo.og_site_name', ''),
            'og_title' => config('seo.og_title', ''),
            'og_description' => config('seo.og_description', ''),
            'og_image' => config('seo.og_image', config('shop.seo_og_image', '')),
            'search_image' => config('seo.search_image', ''),
            'twitter_enabled' => (bool) config('seo.twitter_enabled', true),
            'twitter_card' => config('seo.twitter_card', 'summary_large_image'),
            'twitter_site' => config('seo.twitter_site', ''),
            'twitter_title' => config('seo.twitter_title', ''),
            'twitter_description' => config('seo.twitter_description', ''),
            'twitter_image' => config('seo.twitter_image', ''),
            'google_site_verification' => config('seo.google_site_verification', ''),
            'bing_site_verification' => config('seo.bing_site_verification', ''),
            'yandex_verification' => config('seo.yandex_verification', ''),
            'schema_enabled' => (bool) config('seo.schema_enabled', true),
        ];

        $invoiceSettings = [
            'company_name' => config('invoice.company_name', config('shop.site_name', 'Plant Tech Agro')),
            'address' => config('invoice.address', ''),
            'gst_no' => config('invoice.gst_no', ''),
            'phone' => config('invoice.phone', ''),
            'email' => config('invoice.email', ''),
            'logo' => config('invoice.logo', ''),
            'prefix' => config('invoice.prefix', 'PTA'),
            'terms' => config('invoice.terms', ''),
        ];

        $storedEncryption = config('mail.smtp_encryption', 'tls');

        $mailSettings = [
            'default' => config('mail.default', 'log'),
            'smtp_host' => config('mail.smtp_host') ?? config('mail.mailers.smtp.host', '127.0.0.1'),
            'smtp_port' => config('mail.smtp_port') ?? config('mail.mailers.smtp.port', 2525),
            'smtp_username' => config('mail.smtp_username') ?? config('mail.mailers.smtp.username'),
            'smtp_encryption' => ($storedEncryption === '' || $storedEncryption === null) ? 'none' : $storedEncryption,
            'from_address' => config('mail.from_address') ?? config('mail.from.address', 'hello@example.com'),
            'from_name' => config('mail.from_name') ?? config('mail.from.name', config('app.name', 'PTA Admin')),
            'has_password' => filled(config('mail.smtp_password') ?? config('mail.mailers.smtp.password')),
        ];

        $weatherDistricts = WeatherService::districts();

        $weatherSettings = [
            'enabled' => (bool) config('weather.enabled', true),
            'admin_preview' => (bool) config('weather.admin_preview', true),
            'default_district' => config('weather.default_district', 'srinagar'),
            'cache_ttl_minutes' => config('weather.cache_ttl_minutes', 60),
            'timeout_seconds' => config('weather.timeout_seconds', 5),
            'retries' => config('weather.retries', 2),
            'units' => config('weather.units', 'metric'),
            'timezone' => config('weather.timezone', 'auto'),
            'include_current' => (bool) config('weather.include_current', true),
            'forecast_days' => config('weather.forecast_days', 7),
            'include_hourly' => (bool) config('weather.include_hourly', false),
            'advisory_enabled' => (bool) config('weather.advisory_enabled', true),
            'frost_threshold_c' => config('weather.frost_threshold_c', 2),
            'spray_wind_kmh' => config('weather.spray_wind_kmh', 20),
            'spray_rain_prob' => config('weather.spray_rain_prob', 50),
            'heat_threshold_c' => config('weather.heat_threshold_c', 30),
            'show_admin_card' => (bool) config('weather.show_admin_card', true),
            'show_api_dashboard' => (bool) config('weather.show_api_dashboard', true),
            'show_app_config' => (bool) config('weather.show_app_config', true),
            'api_endpoint_enabled' => (bool) config('weather.api_endpoint_enabled', true),
        ];

        // Never expose the reCAPTCHA secret to the view — only whether one is saved.
        $apisSettings = [
            'recaptcha_enabled' => (bool) config('apis.recaptcha_enabled', false),
            'recaptcha_site_key' => config('apis.recaptcha_site_key', ''),
            'has_secret_key' => filled(config('apis.recaptcha_secret_key')),
            'recaptcha_min_score' => config('apis.recaptcha_min_score', 0.5),
        ];

        return view('admin.settings.index', compact('settings', 'seoSettings', 'invoiceSettings', 'mailSettings', 'palettes', 'fonts', 'sidebarStyles', 'weatherSettings', 'weatherDistricts', 'apisSettings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_email' => 'nullable|email|max:255',
            'site_phone' => 'nullable|string|max:20',
            'site_address' => 'nullable|string|max:500',
            'support_hours' => 'nullable|string|max:255',
            'footer_tagline' => 'nullable|string|max:500',
            'return_policy_text' => 'nullable|string|max:255',
            'theme_palette' => 'nullable|string|in:emerald,blue,indigo,purple,rose,orange,teal,amber',
            'sidebar_style' => 'nullable|string|in:dark,light,brand',
            'font_family' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_whatsapp' => 'nullable|string|max:255',
            'social_x' => 'nullable|url|max:255',
            'seo_meta_title' => 'nullable|string|max:120',
            'seo_meta_description' => 'nullable|string|max:500',
            'seo_meta_keywords' => 'nullable|string|max:500',
            'seo_author' => 'nullable|string|max:255',
            'seo_canonical_url' => 'nullable|url|max:500',
            'seo_robots_index' => 'nullable|in:0,1',
            'seo_robots_follow' => 'nullable|in:0,1',
            'seo_og_enabled' => 'nullable|in:0,1',
            'seo_og_type' => ['nullable', Rule::in(['website', 'article', 'product', 'profile', 'book', 'business.business', 'music.song', 'video.movie'])],
            'seo_og_site_name' => 'nullable|string|max:255',
            'seo_og_title' => 'nullable|string|max:120',
            'seo_og_description' => 'nullable|string|max:500',
            'seo_og_image' => 'nullable|string|max:1000',
            'seo_twitter_enabled' => 'nullable|in:0,1',
            'seo_twitter_card' => ['nullable', Rule::in(['summary', 'summary_large_image', 'app', 'player'])],
            'seo_twitter_site' => 'nullable|string|max:255',
            'seo_twitter_title' => 'nullable|string|max:120',
            'seo_twitter_description' => 'nullable|string|max:500',
            'seo_twitter_image' => 'nullable|string|max:1000',
            'seo_google_site_verification' => 'nullable|string|max:255',
            'seo_bing_site_verification' => 'nullable|string|max:255',
            'seo_yandex_verification' => 'nullable|string|max:255',
            'seo_schema_enabled' => 'nullable|in:0,1',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'favicon_file' => 'nullable|file|mimes:png,jpg,jpeg,webp,gif,ico|max:1024',
            'seo_og_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'seo_twitter_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'seo_search_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'invoice_company_name' => ['required', 'string', 'max:255'],
            'invoice_address' => ['nullable', 'string', 'max:1000'],
            'invoice_gst_no' => ['nullable', 'string', 'max:64'],
            'invoice_phone' => ['nullable', 'string', 'max:20'],
            'invoice_email' => ['nullable', 'email', 'max:255'],
            'invoice_prefix' => ['required', 'string', 'max:16', 'regex:/^[A-Za-z0-9\-]+$/'],
            'invoice_terms' => ['nullable', 'string', 'max:2000'],
            'weather_enabled' => ['nullable', 'in:0,1'],
            'weather_admin_preview' => ['nullable', 'in:0,1'],
            'weather_default_district' => ['nullable', Rule::in(array_keys(WeatherService::districts()))],
            'weather_cache_ttl_minutes' => ['nullable', 'integer', 'min:15', 'max:180'],
            'weather_timeout_seconds' => ['nullable', 'integer', 'min:2', 'max:15'],
            'weather_retries' => ['nullable', 'integer', 'min:0', 'max:3'],
            'weather_units' => ['nullable', Rule::in(['metric', 'imperial'])],
            'weather_timezone' => ['nullable', 'string', 'max:64'],
            'weather_include_current' => ['nullable', 'in:0,1'],
            'weather_forecast_days' => ['nullable', Rule::in([0, 3, 7, 16])],
            'weather_include_hourly' => ['nullable', 'in:0,1'],
            'weather_advisory_enabled' => ['nullable', 'in:0,1'],
            'weather_frost_threshold_c' => ['nullable', 'numeric', 'min:-10', 'max:10'],
            'weather_spray_wind_kmh' => ['nullable', 'numeric', 'min:5', 'max:60'],
            'weather_spray_rain_prob' => ['nullable', 'integer', 'min:0', 'max:100'],
            'weather_heat_threshold_c' => ['nullable', 'numeric', 'min:20', 'max:45'],
            'weather_show_admin_card' => ['nullable', 'in:0,1'],
            'weather_show_api_dashboard' => ['nullable', 'in:0,1'],
            'weather_show_app_config' => ['nullable', 'in:0,1'],
            'weather_api_endpoint_enabled' => ['nullable', 'in:0,1'],
            'apis_recaptcha_enabled' => ['nullable', 'in:0,1'],
            'apis_recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'apis_recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
            'apis_recaptcha_min_score' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        // Per-district coordinate overrides: weather_district_{key}_lat/lon.
        $districtRules = [];
        foreach (array_keys(WeatherService::districts()) as $districtKey) {
            $districtRules["weather_district_{$districtKey}_lat"] = ['nullable', 'numeric', 'between:-90,90'];
            $districtRules["weather_district_{$districtKey}_lon"] = ['nullable', 'numeric', 'between:-180,180'];
        }

        $districtCoords = $request->validate($districtRules);
        $validated = array_merge($validated, $districtCoords);

        $shopSettings = config('shop', []);

        foreach ($validated as $key => $value) {
            if (str_starts_with($key, 'seo_') || str_starts_with($key, 'weather_') || str_starts_with($key, 'apis_') || str_ends_with($key, '_file')) {
                continue;
            }

            if ($key === 'site_name') {
                config(['app.name' => $value]);
            }
            $shopSettings[$key] = $value;
        }

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $path = $file->store('logos', 'public');
            $shopSettings['logo_url'] = '/storage/'.$path;
        } elseif ($request->input('remove_logo') === '1') {
            $shopSettings['logo_url'] = '';
        }

        if ($request->hasFile('favicon_file')) {
            $path = $request->file('favicon_file')->store('favicons', 'public');
            $shopSettings['favicon_url'] = '/storage/'.$path;
        } elseif ($request->input('remove_favicon') === '1') {
            $shopSettings['favicon_url'] = '';
        }

        app(ShopSettingsService::class)->set($shopSettings, 'shop');

        $invoiceSettings = config('invoice', []);

        foreach ($validated as $key => $value) {
            if (str_starts_with($key, 'invoice_')) {
                $invoiceSettings[substr($key, 8)] = $value;
            }
        }

        if ($request->hasFile('invoice_logo_file')) {
            $path = $request->file('invoice_logo_file')->store('logos', 'public');
            $invoiceSettings['logo'] = '/storage/'.$path;
        } elseif ($request->input('remove_invoice_logo') === '1') {
            $invoiceSettings['logo'] = '';
        }

        app(ShopSettingsService::class)->set($invoiceSettings, 'invoice');

        $seoSettings = config('seo', []);

        foreach ($validated as $key => $value) {
            if (str_starts_with($key, 'seo_') && ! str_ends_with($key, '_file')) {
                $seoSettings[substr($key, 4)] = $value;
            }
        }

        foreach (['og_image' => 'seo_og_image_file', 'twitter_image' => 'seo_twitter_image_file', 'search_image' => 'seo_search_image_file'] as $seoKey => $input) {
            if ($request->hasFile($input)) {
                $path = $request->file($input)->store('seo', 'public');
                $seoSettings[$seoKey] = '/storage/'.$path;
            } elseif ($request->input('remove_'.$input) === '1') {
                $seoSettings[$seoKey] = '';
            }
        }

        app(ShopSettingsService::class)->set($seoSettings, 'seo');

        $weatherBools = [
            'enabled', 'admin_preview', 'include_current', 'include_hourly',
            'advisory_enabled', 'show_admin_card', 'show_api_dashboard',
            'show_app_config', 'api_endpoint_enabled',
        ];

        $weather = [];
        foreach ($validated as $key => $value) {
            if (! str_starts_with($key, 'weather_') || preg_match('/^weather_district_.+_(lat|lon)$/', $key)) {
                continue;
            }
            $short = substr($key, 8);
            $weather[$short] = in_array($short, $weatherBools, true)
                ? (($value ?? '0') === '1')
                : $value;
        }

        $districts = WeatherService::districts();
        foreach ($districts as $districtKey => $district) {
            $latKey = "weather_district_{$districtKey}_lat";
            $lonKey = "weather_district_{$districtKey}_lon";
            if (array_key_exists($latKey, $validated) && $validated[$latKey] !== null) {
                $districts[$districtKey]['lat'] = (float) $validated[$latKey];
            }
            if (array_key_exists($lonKey, $validated) && $validated[$lonKey] !== null) {
                $districts[$districtKey]['lon'] = (float) $validated[$lonKey];
            }
        }
        $weather['districts'] = $districts;
        $weather['default_district'] ??= config('weather.default_district', 'srinagar');

        app(ShopSettingsService::class)->set($weather, 'weather');

        $apis = [];
        foreach ($validated as $key => $value) {
            if (! str_starts_with($key, 'apis_')) {
                continue;
            }
            $short = substr($key, 5);
            $apis[$short] = $short === 'recaptcha_enabled'
                ? (($value ?? '0') === '1')
                : $value;
        }

        // Blank secret preserves the saved one (never cleared from this form).
        if (array_key_exists('recaptcha_secret_key', $apis) && ! filled($apis['recaptcha_secret_key'])) {
            unset($apis['recaptcha_secret_key']);
        }

        if ($apis !== []) {
            app(ShopSettingsService::class)->set($apis, 'apis');
        }

        $tab = $request->input('tab', 'general');

        return redirect()->route('admin.settings.index', ['tab' => $tab])
            ->with('success', 'Settings updated successfully.');
    }

    public function smtpUpdate(Request $request)
    {
        $validated = $request->validate([
            'default' => ['required', Rule::in(['smtp', 'log', 'array'])],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', Rule::in(['none', 'tls', 'ssl'])],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $values = [
            'default' => $validated['default'],
            'smtp_host' => filled($validated['smtp_host'] ?? null) ? $validated['smtp_host'] : null,
            'smtp_port' => filled($validated['smtp_port'] ?? null) ? (int) $validated['smtp_port'] : null,
            'smtp_username' => filled($validated['smtp_username'] ?? null) ? $validated['smtp_username'] : null,
            'smtp_encryption' => ($validated['smtp_encryption'] ?? 'none') === 'none' ? '' : $validated['smtp_encryption'],
            'from_address' => $validated['from_address'],
            'from_name' => $validated['from_name'],
            'smtp_password' => (filled($validated['smtp_password'] ?? null) && $validated['smtp_password'] !== '_____')
                ? $validated['smtp_password']
                : (config('mail.smtp_password') ?? config('mail.mailers.smtp.password')),
        ];

        app(ShopSettingsService::class)->set($values, 'mail');

        return redirect()->route('admin.settings.index', ['tab' => 'smtp'])
            ->with('success', 'Mail settings updated. Test sending now to confirm delivery.');
    }

    public function smtpTest(Request $request)
    {
        $fallbackHost = config('mail.mailers.smtp.host');
        $fallbackPort = config('mail.mailers.smtp.port');
        $fallbackUser = config('mail.mailers.smtp.username');
        $fallbackPass = config('mail.mailers.smtp.password');

        $values = [
            'default' => $request->input('default', config('mail.default')),
            'smtp_host' => $request->input('smtp_host', $fallbackHost),
            'smtp_port' => $request->input('smtp_port', $fallbackPort),
            'smtp_username' => $request->input('smtp_username', $fallbackUser),
            'smtp_password' => ($request->input('smtp_password') && $request->input('smtp_password') !== '_____')
                ? $request->input('smtp_password')
                : $fallbackPass,
            'smtp_encryption' => $request->input('smtp_encryption', config('mail.smtp_encryption', 'tls')),
            'from_address' => $request->input('from_address', config('mail.from_address', config('mail.from.address'))),
            'from_name' => $request->input('from_name', config('mail.from_name', config('mail.from.name'))),
        ];

        $values['smtp_encryption'] = $values['smtp_encryption'] === 'none' ? '' : $values['smtp_encryption'];

        try {
            app(MailSettingsService::class)->merge($values);

            $admin = $request->user('admin');

            Mail::raw('Test email from Plant Tech Agro. If you can read this, your SMTP settings are working.', function ($message) use ($admin) {
                $message->to($admin->email)->subject('SMTP Test — Plant Tech Agro');
            });

            return back()->with('success', 'Test email sent to '.$admin->email.'. Check your inbox (and spam folder).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Test email failed: '.$e->getMessage());
        }
    }
}
