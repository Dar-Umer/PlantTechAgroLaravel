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
        'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'customer_area',
        'date', 'valid_until', 'status', 'subtotal', 'discount_total', 'gst_total', 'grand_total',
        'notes', 'terms', 'created_by', 'approved_at',
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
        ];
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
}
