<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    use HasFactory;

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'upi' => 'UPI / QR',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'other' => 'Other',
    ];

    protected $fillable = [
        'invoice_number',
        'pos_customer_id',
        'customer_name',
        'customer_phone',
        'customer_gstin',
        'sale_date',
        'status',
        'subtotal',
        'discount_amount',
        'discount_type',
        'gst_amount',
        'round_off',
        'grand_total',
        'payment_method',
        'amount_tendered',
        'change_amount',
        'payment_reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'round_off' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'amount_tendered' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(PosCustomer::class, 'pos_customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function totalProfit(): float
    {
        return round($this->items->sum(fn ($item) => $item->profitMargin()), 2);
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => ['bg' => 'bg-green-50 text-green-700 border-green-200', 'label' => 'Completed'],
            self::STATUS_CANCELLED => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Cancelled'],
            default => ['bg' => 'bg-gray-100 text-gray-700 border-gray-200', 'label' => ucfirst($this->status)],
        };
    }
}
