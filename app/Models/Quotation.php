<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent to Client',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'draft' => 'gray',
        'sent' => 'blue',
        'approved' => 'green',
        'rejected' => 'red',
    ];

    protected $fillable = [
        'number', 'lead_id', 'customer_id', 'service_id', 'work_order_id',
        'scope_title', 'scope_subtitle', 'variety_name', 'variety_specification', 'rootstock', 'plants_per_kanal',
        'package_title', 'package_poles', 'package_anchors', 'package_plants', 'payment_schedule',
        'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'customer_area',
        'date', 'valid_until', 'status', 'subtotal', 'discount_total', 'gst_total', 'grand_total',
        'notes', 'additional_notes', 'terms',
        'bank_name', 'bank_account_name', 'bank_account_no', 'bank_branch', 'bank_ifsc',
        'company_address', 'company_phone', 'company_email', 'company_website',
        'created_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'valid_until' => 'date',
            'approved_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'gst_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'payment_schedule' => 'array',
            'package_poles' => 'integer',
            'package_anchors' => 'integer',
            'package_plants' => 'integer',
        ];
    }

    /**
     * Get computed payment schedule with amounts matching grand_total.
     */
    public function getCalculatedPaymentSchedule(): array
    {
        $schedule = $this->payment_schedule ?: config('quotation.payment_schedule', []);
        $total = (float) $this->grand_total;

        $result = [];
        foreach ($schedule as $item) {
            $pct = (float) ($item['percent'] ?? 0);
            $stage = $item['stage'] ?? ($item['milestone'] ?? '');
            $amount = isset($item['amount']) && is_numeric($item['amount']) && (float) $item['amount'] > 0
                ? (float) $item['amount']
                : round(($total * $pct) / 100, 2);

            $result[] = [
                'percent' => $pct,
                'stage' => $stage,
                'amount' => $amount,
            ];
        }

        return $result;
    }

    /**
     * Get additional notes as clean array of bullet points.
     */
    public function getAdditionalNotesList(): array
    {
        $raw = $this->additional_notes ?? config('quotation.additional_notes', '');
        if (! $raw) return [];

        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $bullets = [];
        foreach ($lines as $line) {
            $trimmed = trim(ltrim(trim($line), '•-*'));
            if ($trimmed !== '') {
                $bullets[] = $trimmed;
            }
        }
        return $bullets;
    }

    /**
     * Get terms as clean array of numbered strings.
     */
    public function getTermsList(): array
    {
        $raw = $this->terms ?? config('quotation.terms', '');
        if (! $raw) return [];

        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $terms = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                // If it starts with a number like "1. ", strip prefix for structured styling
                $cleaned = preg_replace('/^\d+[\.\)]\s*/', '', $trimmed);
                $terms[] = $cleaned;
            }
        }
        return $terms;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isValid(): bool
    {
        return ! $this->valid_until || $this->valid_until->isFuture() || $this->valid_until->isToday();
    }

    public function canApprove(): bool
    {
        return $this->status !== 'approved' && ! $this->work_order_id && $this->isValid();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'approved' => 'bg-green-50 text-green-700 border-green-200',
            'sent' => 'bg-blue-50 text-blue-700 border-blue-200',
            'rejected' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    /**
     * Determine quotation type based on associated service or stored attributes.
     */
    public function getQuotationType(): string
    {
        if ($this->service) {
            return $this->service->getQuotationType();
        }

        if (! empty($this->package_poles) || (! empty($this->variety_name) && ! empty($this->rootstock))) {
            return 'orchard';
        }

        return 'general';
    }

    /**
     * Should variety details be rendered in document / form?
     */
    public function hasVarietyDetails(): bool
    {
        return in_array($this->getQuotationType(), ['orchard', 'plants'], true)
            || ! empty($this->variety_name);
    }

    /**
     * Should package inclusions (poles, anchors, plants) be rendered?
     */
    public function hasPackageInclusions(): bool
    {
        // 1. If linked to a Service, respect the service designer configuration
        if ($this->service) {
            $defs = $this->service->getQuotationDefaults();
            // If the service explicitly disables package inclusions, NEVER show package
            if (isset($defs['show_package']) && ! $defs['show_package']) {
                return false;
            }

            // If the service allows package, verify at least one spec is present
            if (! empty($defs['show_package'])) {
                return (! empty($this->package_poles) && (int) $this->package_poles > 0)
                    || (! empty($this->package_anchors) && (int) $this->package_anchors > 0)
                    || (! empty($this->package_plants) && (int) $this->package_plants > 0);
            }
        }

        // 2. Standalone quotation without service (only orchard with actual specs)
        if ($this->getQuotationType() !== 'orchard') {
            return false;
        }

        return (! empty($this->package_poles) && (int) $this->package_poles > 0)
            || (! empty($this->package_anchors) && (int) $this->package_anchors > 0)
            || (! empty($this->package_plants) && (int) $this->package_plants > 0);
    }

    /**
     * Header banner title on print/PDF view.
     */
    public function getDocumentTitle(): string
    {
        if ($this->service) {
            return $this->service->getQuotationDefaults()['document_title'] ?? config('quotation.document_title', 'PROFORMA INVOICE');
        }

        return match ($this->getQuotationType()) {
            'orchard' => config('quotation.document_title', 'PROFORMA INVOICE'),
            'plants' => 'PLANT NURSERY BOOKING',
            'installation' => 'INFRASTRUCTURE ESTIMATE',
            'technical' => 'SERVICE ESTIMATE',
            default => config('quotation.document_title', 'PROFORMA INVOICE'),
        };
    }

    /**
     * Header banner subtitle on print/PDF view.
     */
    public function getDocumentSubtitle(): string
    {
        if ($this->service) {
            return $this->service->getQuotationDefaults()['document_subtitle'] ?? config('quotation.document_subtitle', 'PRICE ESTIMATE & QUOTATION');
        }

        return match ($this->getQuotationType()) {
            'orchard' => config('quotation.document_subtitle', 'PRICE ESTIMATE & QUOTATION'),
            'plants' => 'PRICE ESTIMATE & QUOTATION',
            'installation' => 'INSTALLATION & WORK QUOTATION',
            'technical' => 'TESTING & WORK QUOTATION',
            default => config('quotation.document_subtitle', 'PRICE ESTIMATE & QUOTATION'),
        };
    }

    /**
     * Resolve document accent color for headers, tables and badges.
     */
    public function getAccentColor(): string
    {
        if ($this->service) {
            $serviceDefaults = $this->service->getQuotationDefaults();
            if (! empty($serviceDefaults['accent_color'])) {
                return $serviceDefaults['accent_color'];
            }
        }

        return config('quotation.accent_color', '#064e3b');
    }

    /**
     * Resolve company logo for web display.
     */
    public function getCompanyLogoUrl(): ?string
    {
        $candidate = config('shop.logo_url') ?: (config('invoice.logo') ?: config('mobile.app_logo_url'));
        if (! $candidate) {
            return null;
        }

        return \App\Support\Media::url($candidate);
    }

    /**
     * Resolve company logo local file path for DomPDF rendering.
     */
    public function getCompanyLogoDiskPath(): ?string
    {
        $candidate = config('shop.logo_url') ?: (config('invoice.logo') ?: config('mobile.app_logo_url'));
        if (! $candidate) {
            return null;
        }

        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            $parsedPath = parse_url($candidate, PHP_URL_PATH);
            if ($parsedPath) {
                $local = public_path(ltrim($parsedPath, '/'));
                if (is_file($local)) {
                    return $local;
                }
            }
            return null;
        }

        $rel = str_starts_with($candidate, '/') ? ltrim($candidate, '/') : 'storage/'.$candidate;
        $diskPath = public_path($rel);

        return is_file($diskPath) ? $diskPath : null;
    }

    /**
     * Resolve company address with fallbacks to system settings.
     */
    public function getCompanyAddress(): string
    {
        return $this->company_address
            ?: (config('invoice.address')
            ?: (config('shop.site_address')
            ?: config('quotation.address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir')));
    }

    /**
     * Resolve company phone with fallbacks to system settings.
     */
    public function getCompanyPhone(): string
    {
        return $this->company_phone
            ?: (config('invoice.phone')
            ?: (config('shop.site_phone')
            ?: config('quotation.phone', '0194-796-1490')));
    }

    /**
     * Resolve company email with fallbacks to system settings.
     */
    public function getCompanyEmail(): string
    {
        return $this->company_email
            ?: (config('invoice.email')
            ?: (config('shop.site_email')
            ?: config('quotation.email', 'info@plantechagro.com')));
    }

    /**
     * Resolve company website with fallbacks to system settings.
     */
    public function getCompanyWebsite(): string
    {
        return $this->company_website
            ?: (config('shop.site_url')
            ?: (config('invoice.website')
            ?: config('quotation.website', 'www.planttechagro.com')));
    }

    /**
     * Resolve company name with fallbacks to system settings.
     */
    public function getCompanyName(): string
    {
        return config('shop.site_name')
            ?: (config('invoice.company_name')
            ?: config('quotation.company_name', 'Plant Tech Agro'));
    }
}

