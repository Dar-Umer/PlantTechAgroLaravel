<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'no_answer' => 'No Answer',
        'interested' => 'Interested',
        'converted' => 'Converted',
        'lost' => 'Lost',
    ];

    public const STATUS_COLORS = [
        'new' => 'blue',
        'contacted' => 'yellow',
        'no_answer' => 'gray',
        'interested' => 'purple',
        'converted' => 'green',
        'lost' => 'red',
    ];

    protected $fillable = [
        'name', 'phone', 'address', 'service_id', 'service_variation',
        'area', 'unit', 'custom_fields', 'status',
        'notes', 'source', 'converted_customer_id', 'last_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'float',
            'custom_fields' => 'array',
            'last_reminder_sent_at' => 'datetime',
        ];
    }

    public function getAddress(): ?string
    {
        return $this->address ?: ($this->custom_fields['address'] ?? $this->custom_fields['location'] ?? null);
    }

    public function getVariation(): ?string
    {
        return $this->service_variation ?: ($this->custom_fields['service_variation'] ?? $this->custom_fields['variation'] ?? null);
    }

    public function getArea(): ?float
    {
        if ($this->area !== null && $this->area > 0) {
            return (float) $this->area;
        }

        $raw = $this->custom_fields['area'] ?? $this->custom_fields['area_kanals'] ?? $this->custom_fields['kanals'] ?? null;
        if (is_numeric($raw)) {
            return (float) $raw;
        }

        return null;
    }

    public function getUnit(): string
    {
        if ($this->service && ! $this->service->requiresUnit()) {
            return '';
        }

        return $this->unit ?: ($this->service?->default_unit ?: 'Kanal');
    }

    public function formattedRequirement(): string
    {
        if ($this->service && ! $this->service->requiresUnit()) {
            return '';
        }

        $area = $this->getArea();
        if ($area !== null && $area > 0) {
            $unit = $this->getUnit();
            return trim(((float) $area) . ' ' . $unit);
        }

        return '';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class)->latest();
    }

    public function latestQuotation(): HasOne
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    /**
     * A converted lead is a historical record: read-only, never
     * re-convertible, never deletable, status locked.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    /**
     * Has at least one approved quotation linked to this lead.
     */
    public function hasApprovedQuotation(): bool
    {
        if ($this->relationLoaded('quotations')) {
            return $this->quotations->contains(fn ($q) => $q->status === 'approved');
        }

        return $this->quotations()->where('status', 'approved')->exists();
    }

    /**
     * Retrieve the first approved quotation linked to this lead.
     */
    public function approvedQuotation(): ?Quotation
    {
        if ($this->relationLoaded('quotations')) {
            return $this->quotations->first(fn ($q) => $q->status === 'approved');
        }

        return $this->quotations()->where('status', 'approved')->first();
    }

    /**
     * Whether a work order is allowed to be initiated for this lead.
     * Strictly requires quotation approval.
     */
    public function canCreateWorkOrder(): bool
    {
        return ! $this->isConverted() && $this->hasApprovedQuotation();
    }
}
