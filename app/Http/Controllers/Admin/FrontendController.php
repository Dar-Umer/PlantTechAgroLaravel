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
        ];

        $fields = LeadFormField::query()->orderBy('sort_order')->get();
        $sections = HomeSection::query()->orderBy('sort_order')->get();
        $stats = ImpactStat::query()->orderBy('sort_order')->get();
        $activeServicesCount = Service::active()->count();

        return view('admin.frontend.index', compact(
            'tab', 'settings', 'fields', 'sections', 'stats', 'activeServicesCount'
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
}
