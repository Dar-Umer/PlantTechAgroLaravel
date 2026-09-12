<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobileAppController extends Controller
{
    public function index()
    {
        $palettes = config('theme.palettes', []);
        $fonts = config('theme.fonts', []);

        $settings = [
            'app_name' => config('mobile.app_name', ''),
            'splash_tagline' => config('mobile.splash_tagline', ''),
            'app_palette' => config('mobile.app_palette', ''),
            'app_font_family' => config('mobile.app_font_family', ''),
            'app_logo_url' => config('mobile.app_logo_url', ''),
            'version' => config('mobile.version', '1.0.0'),
            'build_number' => config('mobile.build_number', '1'),
            'minimum_supported' => config('mobile.minimum_supported', '1.0.0'),
            'force_update' => (bool) config('mobile.force_update', false),
            'android_update_url' => config('mobile.android_update_url', ''),
            'ios_update_url' => config('mobile.ios_update_url', ''),
            'release_notes' => config('mobile.release_notes', ''),
            'maintenance_mode' => (bool) config('mobile.maintenance_mode', false),
            'echo_otp' => (bool) config('mobile.echo_otp', config('api.echo_otp', true)),
        ];

        return view('admin.mobile-apps.index', compact('settings', 'palettes', 'fonts'));
    }

    public function update(Request $request)
    {
        $paletteKeys = array_keys(config('theme.palettes', []));
        $fontKeys = array_keys(config('theme.fonts', []));

        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:120'],
            'splash_tagline' => ['nullable', 'string', 'max:200'],
            'app_palette' => ['nullable', Rule::in(array_merge([''], $paletteKeys))],
            'app_font_family' => ['nullable', Rule::in(array_merge([''], $fontKeys))],
            'app_logo_url' => ['nullable', 'string', 'max:500'],
            'version' => ['required', 'string', 'max:32', 'regex:/^[0-9]+(\.[0-9]+){0,2}$/'],
            'build_number' => ['nullable', 'string', 'max:32'],
            'minimum_supported' => ['required', 'string', 'max:32', 'regex:/^[0-9]+(\.[0-9]+){0,2}$/'],
            'force_update' => ['nullable', 'in:0,1'],
            'android_update_url' => ['nullable', 'url', 'max:500'],
            'ios_update_url' => ['nullable', 'url', 'max:500'],
            'release_notes' => ['nullable', 'string', 'max:2000'],
            'maintenance_mode' => ['nullable', 'in:0,1'],
            'echo_otp' => ['nullable', 'in:0,1'],
            'app_logo_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
        ]);

        $settings = [
            'app_name' => $validated['app_name'] ?? '',
            'splash_tagline' => $validated['splash_tagline'] ?? '',
            'app_palette' => $validated['app_palette'] ?? '',
            'app_font_family' => $validated['app_font_family'] ?? '',
            'app_logo_url' => config('mobile.app_logo_url', ''),
            'version' => $validated['version'],
            'build_number' => $validated['build_number'] ?? '',
            'minimum_supported' => $validated['minimum_supported'],
            'force_update' => ($validated['force_update'] ?? '0') === '1',
            'android_update_url' => $validated['android_update_url'] ?? '',
            'ios_update_url' => $validated['ios_update_url'] ?? '',
            'release_notes' => $validated['release_notes'] ?? '',
            'maintenance_mode' => ($validated['maintenance_mode'] ?? '0') === '1',
            'echo_otp' => ($validated['echo_otp'] ?? '0') === '1',
        ];

        if ($request->hasFile('app_logo_file')) {
            $path = $request->file('app_logo_file')->store('logos', 'public');
            $settings['app_logo_url'] = '/storage/'.$path;
        } elseif ($request->input('remove_app_logo') === '1') {
            $settings['app_logo_url'] = '';
        }

        app(ShopSettingsService::class)->set($settings, 'mobile');

        return redirect()->route('admin.mobile-apps.index', ['tab' => $request->input('tab', 'appearance')])
            ->with('success', 'Mobile app settings updated.');
    }
}