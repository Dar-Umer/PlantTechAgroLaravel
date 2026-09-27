<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\HtmlSanitizer;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount(['stages', 'items'])->orderBy('sort_order')->paginate(15);

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $service = new Service();
        $presets = Service::getAllPresetDefaults();

        return view('admin.services.create', compact('service', 'presets'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAndPrepareServiceData($request);
        $data['slug'] = Str::slug($data['name']);

        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        $presets = Service::getAllPresetDefaults();

        return view('admin.services.edit', compact('service', 'presets'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateAndPrepareServiceData($request, $service);

        $service->update($data);

        $tab = $request->input('tab');
        if ($tab && in_array($tab, ['basic', 'quotation', 'invoice', 'workflow'], true)) {
            return redirect()->route('admin.services.edit', [$service, 'tab' => $tab])->with('success', 'Service updated successfully.');
        }

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    protected function validateAndPrepareServiceData(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'book_url' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'creates_orchard_on_completion' => ['boolean'],

            // Quotation & Package settings
            'quotation_type' => ['nullable', 'string', 'in:orchard,plants,installation,technical,call,general'],
            'quotation_title' => ['nullable', 'string', 'max:120'],
            'quotation_subtitle' => ['nullable', 'string', 'max:150'],
            'accent_color' => ['nullable', 'string', 'max:25'],
            'show_variety' => ['nullable', 'boolean'],
            'show_package' => ['nullable', 'boolean'],
            'package_title' => ['nullable', 'string', 'max:150'],
            'package_poles' => ['nullable', 'integer', 'min:0'],
            'package_anchors' => ['nullable', 'integer', 'min:0'],
            'package_plants' => ['nullable', 'integer', 'min:0'],
            'variety_name' => ['nullable', 'string', 'max:150'],
            'rootstock' => ['nullable', 'string', 'max:150'],
            'plants_per_kanal' => ['nullable', 'string', 'max:150'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'variations' => ['nullable', 'array'],
            'variations.*.name' => ['nullable', 'string', 'max:255'],
            'variations.*.plants_per_kanal' => ['nullable'],
            'variations.*.package_poles' => ['nullable'],
            'variations.*.package_anchors' => ['nullable'],
            'variations.*.package_plants' => ['nullable'],
            'variations.*.rate' => ['nullable'],
            'variations.*.unit' => ['nullable', 'string', 'max:50'],
            'variations.*.rootstock' => ['nullable', 'string', 'max:150'],
            'variations.*.variety_name' => ['nullable', 'string', 'max:150'],
            'payment_schedule' => ['nullable', 'array'],
            'payment_schedule.*.percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_schedule.*.stage' => ['nullable', 'string', 'max:255'],
            'additional_notes' => ['nullable', 'string', 'max:3000'],
            'quotation_terms' => ['nullable', 'string', 'max:3000'],

            // Invoice settings
            'invoice_title' => ['nullable', 'string', 'max:120'],
            'invoice_subtitle' => ['nullable', 'string', 'max:150'],
            'invoice_prefix' => ['nullable', 'string', 'max:30'],
            'invoice_terms' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['creates_orchard_on_completion'] = (bool) $request->boolean('creates_orchard_on_completion');

        if (isset($data['image']) && $data['image']) {
            $data['image'] = Media::storeImage($request->file('image'), 'services');
        } elseif ($service) {
            unset($data['image']);
        }

        $data['content'] = HtmlSanitizer::clean($data['content'] ?? ($service?->content ?? null));
        if (array_key_exists('description', $data)) {
            $data['description'] = strip_tags((string) $data['description']);
        }

        // If quotation settings are provided in the form, construct quotation_settings array
        if ($request->has('quotation_type') || $request->has('has_quotation_settings')) {
            $cleanSchedule = [];
            if (!empty($data['payment_schedule']) && is_array($data['payment_schedule'])) {
                foreach ($data['payment_schedule'] as $step) {
                    if (!empty($step['stage']) || isset($step['percent'])) {
                        $cleanSchedule[] = [
                            'percent' => (float) ($step['percent'] ?? 0),
                            'stage' => (string) ($step['stage'] ?? ''),
                        ];
                    }
                }
            }

            $cleanVariations = [];
            if (!empty($data['variations']) && is_array($data['variations'])) {
                foreach ($data['variations'] as $v) {
                    if (!empty($v['name'])) {
                        $cleanVariations[] = [
                            'name' => (string) $v['name'],
                            'plants_per_kanal' => (string) ($v['plants_per_kanal'] ?? ''),
                            'package_poles' => isset($v['package_poles']) && $v['package_poles'] !== '' ? (int) $v['package_poles'] : null,
                            'package_anchors' => isset($v['package_anchors']) && $v['package_anchors'] !== '' ? (int) $v['package_anchors'] : null,
                            'package_plants' => isset($v['package_plants']) && $v['package_plants'] !== '' ? (int) $v['package_plants'] : null,
                            'rate' => isset($v['rate']) && $v['rate'] !== '' ? (float) $v['rate'] : 0,
                            'unit' => (string) ($v['unit'] ?? 'Kanal'),
                            'rootstock' => (string) ($v['rootstock'] ?? ''),
                            'variety_name' => (string) ($v['variety_name'] ?? ''),
                        ];
                    }
                }
            }

            $hasUnit = $request->has('has_unit')
                ? (bool) $request->boolean('has_unit')
                : (($data['quotation_type'] ?? '') !== 'call');

            $data['quotation_settings'] = [
                'quotation_type' => $data['quotation_type'] ?? ($service?->getQuotationType() ?? 'orchard'),
                'document_title' => $data['quotation_title'] ?? 'PROFORMA INVOICE',
                'document_subtitle' => $data['quotation_subtitle'] ?? '',
                'accent_color' => !empty($data['accent_color']) ? $data['accent_color'] : '#064e3b',
                'show_variety' => (bool) $request->boolean('show_variety'),
                'show_package' => (bool) $request->boolean('show_package'),
                'has_unit' => $hasUnit,
                'requires_unit' => $hasUnit,
                'package_title' => $data['package_title'] ?? '',
                'package_poles' => $request->filled('package_poles') ? (int) $data['package_poles'] : null,
                'package_anchors' => $request->filled('package_anchors') ? (int) $data['package_anchors'] : null,
                'package_plants' => $request->filled('package_plants') ? (int) $data['package_plants'] : null,
                'variety_name' => $data['variety_name'] ?? '',
                'rootstock' => $data['rootstock'] ?? '',
                'plants_per_kanal' => $data['plants_per_kanal'] ?? '',
                'base_price' => $request->filled('base_price') ? (float) $data['base_price'] : null,
                'unit' => $hasUnit ? ($data['unit'] ?? 'Kanal') : ($data['unit'] ?: 'Fixed'),
                'variations' => $cleanVariations,
                'payment_schedule' => $cleanSchedule,
                'additional_notes' => $data['additional_notes'] ?? '',
                'terms' => $data['quotation_terms'] ?? '',
            ];

            if ($request->has('invoice_title') || $request->has('invoice_prefix') || $request->has('invoice_terms')) {
                $data['invoice_settings'] = [
                    'document_title' => !empty($data['invoice_title']) ? $data['invoice_title'] : 'TAX INVOICE',
                    'document_subtitle' => !empty($data['invoice_subtitle']) ? $data['invoice_subtitle'] : ($service?->name ?? $data['name'] ?? ''),
                    'invoice_prefix' => $data['invoice_prefix'] ?? '',
                    'accent_color' => !empty($data['accent_color']) ? $data['accent_color'] : '#16a34a',
                    'terms' => $data['invoice_terms'] ?? '',
                ];
            }
        }

        // Clean individual fields that were grouped into quotation_settings/invoice_settings
        unset(
            $data['quotation_type'], $data['quotation_title'], $data['quotation_subtitle'],
            $data['accent_color'], $data['show_variety'], $data['show_package'], $data['has_unit'],
            $data['package_title'], $data['package_poles'], $data['package_anchors'], $data['package_plants'],
            $data['variety_name'], $data['rootstock'], $data['plants_per_kanal'],
            $data['base_price'], $data['unit'], $data['variations'], $data['payment_schedule'],
            $data['additional_notes'], $data['quotation_terms'],
            $data['invoice_title'], $data['invoice_subtitle'], $data['invoice_prefix'], $data['invoice_terms']
        );

        return $data;
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }
}
