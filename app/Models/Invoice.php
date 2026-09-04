<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUSES = [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'unpaid' => 'red',
        'partial' => 'yellow',
        'paid' => 'green',
        'cancelled' => 'gray',
    ];

    protected $fillable = [
        'number', 'customer_id', 'customer_name', 'work_order_id', 'invoice_date', 'due_date',
        'status', 'subtotal', 'discount_total', 'gst_total', 'grand_total', 'amount_paid',
        'terms', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'gst_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function balanceDue(): float
    {
        return max(0, round((float) $this->grand_total - (float) $this->amount_paid, 2));
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
