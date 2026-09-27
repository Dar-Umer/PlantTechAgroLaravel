<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'content', 'icon', 'image',
        'category', 'book_url', 'sort_order', 'is_active', 'creates_orchard_on_completion',
        'quotation_settings', 'invoice_settings',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class)->orderBy('sort_order');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ServiceStage::class)->orderBy('sort_order');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'creates_orchard_on_completion' => 'boolean',
            'quotation_settings' => 'array',
            'invoice_settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    /**
     * Determine the quotation style and required input fields for this service.
     * Supported types: orchard, plants, installation, technical, general.
     */
    public function getQuotationType(): string
    {
        if (! empty($this->quotation_settings['quotation_type'])) {
            return $this->quotation_settings['quotation_type'];
        }

        if (in_array($this->slug, ['book-plants', 'book-plant'], true) || str_contains($this->slug, 'plant')) {
            return 'plants';
        }

        if (in_array($this->slug, ['book-orchard', 'book-an-orchard'], true)
            || str_contains($this->slug, 'orchard')
            || ($this->creates_orchard_on_completion && $this->category === 'orchard-development')) {
            return 'orchard';
        }

        if (in_array($this->slug, ['book-hail-protection', 'hail-netting', 'trellis-installation'], true)
            || $this->category === 'hail-protection'
            || str_contains($this->slug, 'hail')
            || str_contains($this->slug, 'trellis')) {
            return 'installation';
        }

        if (in_array($this->category, ['soil-health-management', 'ground-water-detection'], true)
            || in_array($this->slug, ['book-soil-test', 'book-ground-water-detection'], true)
            || (str_contains($this->slug, 'soil') || str_contains($this->slug, 'water-detection'))) {
            return 'technical';
        }

        if (in_array($this->slug, ['book-expert-call', 'book-a-call', 'book-call'], true)
            || str_contains($this->slug, 'call')
            || str_contains(strtolower($this->name ?? ''), 'call')
            || $this->category === 'orchard-consultation') {
            return 'call';
        }

        return 'general';
    }

    /**
     * Get all preset template defaults for service creation/designer.
     */
    public static function getAllPresetDefaults(): array
    {
        $types = ['orchard', 'plants', 'installation', 'technical', 'call', 'general'];
        $presets = [];
        foreach ($types as $type) {
            $dummy = new self(['quotation_settings' => ['quotation_type' => $type]]);
            $presets[$type] = $dummy->getQuotationDefaults();
        }

        return $presets;
    }

    /**
     * Get style presets, default input visibility, and milestone templates for this service type.
     */
    public function getQuotationDefaults(): array
    {
        $type = $this->getQuotationType();

        $globalTerms = config('quotation.terms', "1. This quotation is valid until the specified expiry date.\n2. Advance payment at the time of booking confirms the order.\n3. Final measurements and quantities are subject to site verification.\n4. Any extra work or materials, not included in this quotation, will be billed separately.\n5. Booking is subject to availability of plants and materials.\n6. Cancellation and refund policy will be as per company terms and conditions.\n7. Prices are subject to change without prior notice due to market variations.");

        $defaults = match ($type) {
            'orchard' => [
                'type' => 'orchard',
                'label' => 'High-Density Orchard Establishment',
                'document_title' => 'PROFORMA INVOICE',
                'document_subtitle' => 'PRICE ESTIMATE & QUOTATION',
                'accent_color' => '#064e3b',
                'design_style' => 'orchard_proforma',
                'show_variety' => true,
                'show_package' => true,
                'package_title' => 'Per Kanal Standard Package',
                'package_poles' => 19,
                'package_anchors' => 6,
                'package_plants' => 150,
                'variety_name' => 'Devil Gala',
                'rootstock' => 'M9 / T337 (High Density)',
                'plants_per_kanal' => '150 (Standard)',
                'base_price' => 185000,
                'has_unit' => true,
                'requires_unit' => true,
                'unit' => 'Kanal',
                'variations' => [
                    [
                        'name' => '150 Plants / Kanal (Standard High Density)',
                        'plants_per_kanal' => '150 (Standard)',
                        'package_poles' => 19,
                        'package_anchors' => 6,
                        'package_plants' => 150,
                        'rate' => 185000,
                        'unit' => 'Kanal',
                        'rootstock' => 'M9 / T337 (High Density)',
                        'variety_name' => 'Devil Gala',
                    ],
                    [
                        'name' => '170 Plants / Kanal (Ultra High Density)',
                        'plants_per_kanal' => '170 (Ultra High Density)',
                        'package_poles' => 22,
                        'package_anchors' => 8,
                        'package_plants' => 170,
                        'rate' => 210000,
                        'unit' => 'Kanal',
                        'rootstock' => 'M9 / T337 (High Density)',
                        'variety_name' => 'Devil Gala',
                    ],
                ],
                'payment_schedule' => [
                    ['percent' => 30, 'stage' => 'Advance at the time of booking'],
                    ['percent' => 50, 'stage' => 'Before trellis installation'],
                    ['percent' => 20, 'stage' => 'Before plantation'],
                ],
                'additional_notes' => "Extra anchors, if required, will be charged separately at ₹ 2,500 per anchor, including all accessories.\nExtra plants, if required, will be charged separately at ₹ 1,230 per plant, including all accessories.",
                'terms' => $globalTerms,
            ],
            'plants' => [
                'type' => 'plants',
                'label' => 'Nursery & Plant Supply',
                'document_title' => 'PLANT NURSERY BOOKING',
                'document_subtitle' => 'PRICE ESTIMATE & QUOTATION',
                'accent_color' => '#15803d',
                'design_style' => 'modern_clean',
                'show_variety' => true,
                'show_package' => false,
                'package_title' => '',
                'package_poles' => null,
                'package_anchors' => null,
                'package_plants' => null,
                'variety_name' => 'Devil Gala',
                'rootstock' => 'M9 / T337 (High Density)',
                'plants_per_kanal' => 'N/A',
                'base_price' => 450,
                'has_unit' => true,
                'requires_unit' => true,
                'unit' => 'Plant',
                'variations' => [
                    [
                        'name' => 'Standard Bare-Root Plant (M9 / T337)',
                        'plants_per_kanal' => 'N/A',
                        'package_poles' => null,
                        'package_anchors' => null,
                        'package_plants' => 1,
                        'rate' => 450,
                        'unit' => 'Plant',
                        'rootstock' => 'M9 / T337 (High Density)',
                        'variety_name' => 'Devil Gala',
                    ],
                    [
                        'name' => 'Knip Boom (2-Year Branching Rootstock)',
                        'plants_per_kanal' => 'N/A',
                        'package_poles' => null,
                        'package_anchors' => null,
                        'package_plants' => 1,
                        'rate' => 580,
                        'unit' => 'Plant',
                        'rootstock' => 'M9 / T337 (High Density)',
                        'variety_name' => 'Devil Gala',
                    ],
                ],
                'payment_schedule' => [
                    ['percent' => 40, 'stage' => 'Advance booking confirmation'],
                    ['percent' => 60, 'stage' => 'Prior to nursery dispatch / delivery'],
                ],
                'additional_notes' => "Plant health and certified rootstock verified prior to dispatch.\nTransportation and handling charges will be billed as per actual distance.\nPlanting guidelines must be strictly adhered to upon arrival at the orchard.",
                'terms' => $globalTerms,
            ],
            'installation' => [
                'type' => 'installation',
                'label' => 'Netting & Trellis Installation',
                'document_title' => 'INFRASTRUCTURE ESTIMATE',
                'document_subtitle' => 'INSTALLATION & WORK QUOTATION',
                'accent_color' => '#0f766e',
                'design_style' => 'orchard_proforma',
                'show_variety' => false,
                'show_package' => true,
                'package_title' => 'Hail Protection Structure Package',
                'package_poles' => 20,
                'package_anchors' => 8,
                'package_plants' => 0,
                'variety_name' => '',
                'rootstock' => '',
                'plants_per_kanal' => '',
                'base_price' => 115000,
                'has_unit' => true,
                'requires_unit' => true,
                'unit' => 'Kanal',
                'variations' => [
                    [
                        'name' => 'Standard Hail Netting & Trellis (20 Poles)',
                        'plants_per_kanal' => '',
                        'package_poles' => 20,
                        'package_anchors' => 8,
                        'package_plants' => 0,
                        'rate' => 115000,
                        'unit' => 'Kanal',
                        'rootstock' => '',
                        'variety_name' => '',
                    ],
                    [
                        'name' => 'Heavy Duty Wind & Hail Netting (24 Poles)',
                        'plants_per_kanal' => '',
                        'package_poles' => 24,
                        'package_anchors' => 10,
                        'package_plants' => 0,
                        'rate' => 135000,
                        'unit' => 'Kanal',
                        'rootstock' => '',
                        'variety_name' => '',
                    ],
                ],
                'payment_schedule' => [
                    ['percent' => 40, 'stage' => 'Advance on material procurement'],
                    ['percent' => 40, 'stage' => 'On structure and pole erection'],
                    ['percent' => 20, 'stage' => 'On net tensioning & final inspection'],
                ],
                'additional_notes' => "Anchor clearing and perimeter fence boundary is to be facilitated by client.\nSevere weather or wind conditions may adjust the field installation schedule.\nStructure materials carry standard manufacturer warranty.",
                'terms' => $globalTerms,
            ],
            'technical' => [
                'type' => 'technical',
                'label' => 'Soil & Analytical Field Services',
                'document_title' => 'SERVICE ESTIMATE',
                'document_subtitle' => 'TESTING & WORK QUOTATION',
                'accent_color' => '#0369a1',
                'design_style' => 'modern_clean',
                'show_variety' => false,
                'show_package' => false,
                'package_title' => '',
                'package_poles' => null,
                'package_anchors' => null,
                'package_plants' => null,
                'variety_name' => '',
                'rootstock' => '',
                'plants_per_kanal' => '',
                'base_price' => 3500,
                'has_unit' => true,
                'requires_unit' => true,
                'unit' => 'Sample',
                'variations' => [
                    [
                        'name' => 'Complete Soil Profile & Chemical Analysis',
                        'plants_per_kanal' => '',
                        'package_poles' => null,
                        'package_anchors' => null,
                        'package_plants' => null,
                        'rate' => 3500,
                        'unit' => 'Sample',
                        'rootstock' => '',
                        'variety_name' => '',
                    ],
                    [
                        'name' => 'Ground Water Detection & Hydro-Geological Survey',
                        'plants_per_kanal' => '',
                        'package_poles' => null,
                        'package_anchors' => null,
                        'package_plants' => null,
                        'rate' => 12000,
                        'unit' => 'Site',
                        'rootstock' => '',
                        'variety_name' => '',
                    ],
                ],
                'payment_schedule' => [
                    ['percent' => 50, 'stage' => 'Advance at the time of booking'],
                    ['percent' => 50, 'stage' => 'Upon submission of final analysis report'],
                ],
                'additional_notes' => "Field sampling and visit dates will be coordinated 24 hours in advance.\nLaboratory analysis test report will be delivered within 5 business days.\nAgronomist recommendations will be provided alongside analytical readings.",
                'terms' => $globalTerms,
            ],
            'call' => [
                'type' => 'call',
                'label' => 'Expert Agronomy Call & Advisory',
                'document_title' => 'CONSULTATION SUMMARY',
                'document_subtitle' => 'BOOK A CALL & EXPERT ADVISORY',
                'accent_color' => '#4338ca',
                'design_style' => 'modern_clean',
                'show_variety' => false,
                'show_package' => false,
                'package_title' => '',
                'package_poles' => null,
                'package_anchors' => null,
                'package_plants' => null,
                'variety_name' => '',
                'rootstock' => '',
                'plants_per_kanal' => '',
                'base_price' => 0,
                'has_unit' => false,
                'requires_unit' => false,
                'unit' => 'Call',
                'variations' => [],
                'payment_schedule' => [
                    ['percent' => 100, 'stage' => 'Booking confirmation'],
                ],
                'additional_notes' => "Our orchard agronomist will connect with you via phone / video call on your scheduled date and time.\nTopics covered: soil health, high-density varieties, pruning techniques, and disease prevention.",
                'terms' => $globalTerms,
            ],
            default => [
                'type' => 'general',
                'label' => 'General Agriculture Service',
                'document_title' => 'PROFORMA INVOICE',
                'document_subtitle' => 'PRICE ESTIMATE & QUOTATION',
                'accent_color' => '#064e3b',
                'design_style' => 'orchard_proforma',
                'show_variety' => false,
                'show_package' => false,
                'package_title' => '',
                'package_poles' => null,
                'package_anchors' => null,
                'package_plants' => null,
                'variety_name' => '',
                'rootstock' => '',
                'plants_per_kanal' => '',
                'base_price' => 0,
                'has_unit' => true,
                'requires_unit' => true,
                'unit' => 'Job',
                'variations' => [],
                'payment_schedule' => [
                    ['percent' => 50, 'stage' => 'Advance on booking confirmation'],
                    ['percent' => 50, 'stage' => 'Upon completion of work'],
                ],
                'additional_notes' => '',
                'terms' => $globalTerms,
            ],
        };

        if (! empty($this->quotation_settings) && is_array($this->quotation_settings)) {
            foreach ($this->quotation_settings as $key => $val) {
                if ($val !== null && $val !== '') {
                    $defaults[$key] = $val;
                }
            }
        }

        return $defaults;
    }

    /**
     * Determine whether this service requires an area/unit input in the booking form.
     */
    public function requiresUnit(): bool
    {
        if (isset($this->quotation_settings['has_unit'])) {
            return (bool) $this->quotation_settings['has_unit'];
        }

        if (isset($this->quotation_settings['requires_unit'])) {
            return (bool) $this->quotation_settings['requires_unit'];
        }

        if ($this->getQuotationType() === 'call') {
            return false;
        }

        $unit = strtolower(trim((string) ($this->quotation_settings['unit'] ?? '')));
        if (in_array($unit, ['none', 'no unit', 'n/a'], true) && ! empty($this->quotation_settings)) {
            return false;
        }

        return true;
    }

    /**
     * Get invoice styling and presets for this service.
     */
    public function getInvoiceDefaults(): array
    {
        $base = [
            'document_title' => 'TAX INVOICE',
            'document_subtitle' => $this->name,
            'accent_color' => $this->quotation_settings['accent_color'] ?? '#064e3b',
            'invoice_prefix' => config('invoice.prefix', 'PTA'),
            'terms' => config('invoice.terms', ''),
            'notes' => '',
        ];

        if (! empty($this->invoice_settings) && is_array($this->invoice_settings)) {
            foreach ($this->invoice_settings as $key => $val) {
                if ($val !== null && $val !== '') {
                    $base[$key] = $val;
                }
            }
            if (!empty($this->invoice_settings['doc_title'])) {
                $base['document_title'] = $this->invoice_settings['doc_title'];
            }
            if (!empty($this->invoice_settings['doc_subtitle'])) {
                $base['document_subtitle'] = $this->invoice_settings['doc_subtitle'];
            }
        }

        return $base;
    }
}
