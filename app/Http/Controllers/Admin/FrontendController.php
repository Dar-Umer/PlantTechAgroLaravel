<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\ImpactStat;
use App\Models\LeadFormField;
use App\Models\Service;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'lead_form');

        $settings = [
            'lead_form_heading' => config('frontend.lead_form.heading'),
            'lead_form_description' => config('frontend.lead_form.description'),
            'lead_form_button_text' => config('frontend.lead_form.button_text'),
            'lead_form_success_message' => config('frontend.lead_form.success_message'),
            'site_name' => config('shop.site_name'),
            'site_email' => config('shop.site_email'),
            'site_phone' => config('shop.site_phone'),
            'support_hours' => config('shop.support_hours'),
            'site_address' => config('shop.site_address'),
            'footer_tagline' => config('shop.footer_tagline'),
            'social_facebook' => config('shop.social_facebook'),
            'social_instagram' => config('shop.social_instagram'),
            'social_youtube' => config('shop.social_youtube'),
            'social_whatsapp' => config('shop.social_whatsapp'),
            'social_x' => config('shop.social_x'),
            'support_hours' => config('shop.support_hours'),
        ];

        $fields = LeadFormField::query()->orderBy('sort_order')->get();
        $sections = HomeSection::query()->orderBy('sort_order')->get();
        $stats = ImpactStat::query()->orderBy('sort_order')->get();
        $activeServicesCount = Service::active()->count();

        $noticeBar = config('frontend.notice_bar', []);
        $notice = [
            'enabled' => (bool) ($noticeBar['enabled'] ?? false),
            'items' => array_values(array_filter(array_map('trim', (array) ($noticeBar['items'] ?? [])))),
            'speed' => max(10, min(120, (int) ($noticeBar['speed'] ?? 40))),
        ];

        return view('admin.frontend.index', compact(
            'tab', 'settings', 'fields', 'sections', 'stats', 'activeServicesCount', 'notice'
        ));
    }

    public function updateLeadForm(Request $request, ShopSettingsService $settingsService)
    {
        $validated = $request->validate([
            'lead_form_heading' => ['required', 'string', 'max:255'],
            'lead_form_description' => ['nullable', 'string', 'max:1000'],
            'lead_form_button_text' => ['required', 'string', 'max:100'],
            'lead_form_success_message' => ['required', 'string', 'max:1000'],
        ]);

        $settingsService->set([
            'lead_form' => [
                'heading' => $validated['lead_form_heading'],
                'description' => $validated['lead_form_description'] ?? '',
                'button_text' => $validated['lead_form_button_text'],
                'success_message' => $validated['lead_form_success_message'],
            ],
        ], 'frontend');

        return redirect()->route('admin.frontend.index', ['tab' => 'lead_form'])
            ->with('success', 'Lead form settings updated.');
    }

    public function updateHomeSections(Request $request)
    {
        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.subtitle' => ['nullable', 'string', 'max:1000'],
            'sections.*.description' => ['nullable', 'string'],
            'sections.*.is_active' => ['nullable', 'boolean'],
            'sections.*.points' => ['nullable', 'array'],
            'sections.*.points.*' => ['nullable', 'string', 'max:255'],
            'sections.*.image_1' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'sections.*.image_2' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'sections.*.remove_image_1' => ['nullable', 'boolean'],
            'sections.*.remove_image_2' => ['nullable', 'boolean'],
            'stats' => ['nullable', 'array'],
            'stats.*.label' => ['nullable', 'string', 'max:255'],
            'stats.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated['sections'] ?? [] as $id => $input) {
            $section = HomeSection::find($id);
            if (! $section) {
                continue;
            }

            $content = $section->content ?? [];

            foreach (['image_1', 'image_2'] as $imageKey) {
                if (! empty($input['remove_'.$imageKey])) {
                    $content[$imageKey] = null;
                } elseif ($request->hasFile("sections.{$id}.{$imageKey}")) {
                    $content[$imageKey] = $request->file("sections.{$id}.{$imageKey}")->store('sections', 'public');
                }
            }

            if (array_key_exists('points', $input)) {
                $content['points'] = array_values(array_filter(
                    array_map('trim', (array) $input['points'])
                ));
            }

            if (array_key_exists('description', $input)) {
                $content['description'] = $input['description'];
            }

            $section->update([
                'title' => $input['title'] ?? $section->title,
                'subtitle' => $input['subtitle'] ?? $section->subtitle,
                'content' => $content,
                'is_active' => isset($input['is_active']),
            ]);
        }

        foreach ($validated['stats'] ?? [] as $id => $input) {
            $stat = ImpactStat::find($id);
            if (! $stat) {
                continue;
            }

            $stat->update([
                'label' => $input['label'] ?? $stat->label,
                'value' => $input['value'] ?? $stat->value,
            ]);
        }

        return redirect()->route('admin.frontend.index', ['tab' => 'home_sections'])
            ->with('success', 'Home sections updated.');
    }

    public function updateFooter(Request $request, ShopSettingsService $settingsService)
    {
        $validated = $request->validate([
            'footer_heading' => ['nullable', 'string', 'max:255'],
            'footer_tagline' => ['nullable', 'string', 'max:1000'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:50'],
            'site_address' => ['nullable', 'string', 'max:1000'],
            'support_hours' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:500'],
            'social_instagram' => ['nullable', 'string', 'max:500'],
            'social_youtube' => ['nullable', 'string', 'max:500'],
            'social_whatsapp' => ['nullable', 'string', 'max:500'],
            'social_x' => ['nullable', 'string', 'max:500'],
        ]);

        $settingsService->set([
            'site_name' => $validated['site_name'] ?? config('shop.site_name'),
            'footer_tagline' => $validated['footer_tagline'] ?? '',
            'site_email' => $validated['site_email'] ?? '',
            'site_phone' => $validated['site_phone'] ?? '',
            'site_address' => $validated['site_address'] ?? '',
            'support_hours' => $validated['support_hours'] ?? '',
            'social_facebook' => $validated['social_facebook'] ?? '',
            'social_instagram' => $validated['social_instagram'] ?? '',
            'social_youtube' => $validated['social_youtube'] ?? '',
            'social_whatsapp' => $validated['social_whatsapp'] ?? '',
            'social_x' => $validated['social_x'] ?? '',
        ], 'shop');

        return redirect()->route('admin.frontend.index', ['tab' => 'footer'])
            ->with('success', 'Footer settings updated.');
    }

    public function updateNotice(Request $request, ShopSettingsService $settingsService)
    {
        $validated = $request->validate([
            'notice_enabled' => ['nullable', 'in:0,1'],
            'notice_items' => ['nullable', 'array', 'max:5'],
            'notice_items.*' => ['nullable', 'string', 'max:255'],
            'notice_speed' => ['nullable', 'integer', 'min:10', 'max:120'],
        ]);

        $items = array_values(array_filter(array_map('trim', (array) ($validated['notice_items'] ?? []))));

        $settingsService->set([
            'notice_bar' => [
                'enabled' => ($validated['notice_enabled'] ?? '0') === '1',
                'items' => $items,
                'speed' => max(10, min(120, (int) ($validated['notice_speed'] ?? 40))),
            ],
        ], 'frontend');

        return redirect()->route('admin.frontend.index', ['tab' => 'notice'])
            ->with('success', 'Notice bar updated.');
    }
}
