<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;

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
            'social_facebook' => config('shop.social_facebook', ''),
            'social_instagram' => config('shop.social_instagram', ''),
            'social_youtube' => config('shop.social_youtube', ''),
            'social_whatsapp' => config('shop.social_whatsapp', ''),
            'social_x' => config('shop.social_x', ''),
            'seo_meta_description' => config('shop.seo_meta_description', ''),
            'seo_meta_keywords' => config('shop.seo_meta_keywords', ''),
            'seo_og_image' => config('shop.seo_og_image', ''),
        ];

        return view('admin.settings.index', compact('settings', 'palettes', 'fonts', 'sidebarStyles'));
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
            'seo_meta_description' => 'nullable|string|max:500',
            'seo_meta_keywords' => 'nullable|string|max:500',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $shopSettings = config('shop', []);

        foreach ($validated as $key => $value) {
            if ($key === 'site_name') {
                config(['app.name' => $value]);
            }
            $shopSettings[$key] = $value;
        }

        // Handle logo upload
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $path = $file->store('logos', 'public');
            $shopSettings['logo_url'] = '/storage/'.$path;
        } elseif ($request->input('remove_logo') === '1') {
            $shopSettings['logo_url'] = '';
        }

        app(ShopSettingsService::class)->set($shopSettings, 'shop');

        $tab = $request->input('tab', 'general');

        return redirect()->route('admin.settings.index', ['tab' => $tab])
            ->with('success', 'Settings updated successfully.');
    }
}
