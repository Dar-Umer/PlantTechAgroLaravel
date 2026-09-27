<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'supplier_id',
        'supplier_invoice_no',
        'bill_date',
        'due_date',
        'status',
        'payment_status',
        'subtotal',
        'discount',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'paid_amount',
        'balance_due',
        'notes',
        'attachment',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_UNPAID => 'Unpaid',
        self::PAYMENT_STATUS_PARTIAL => 'Partially Paid',
        self::PAYMENT_STATUS_PAID => 'Fully Paid',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseBillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function refreshPaymentStatus(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $total = (float) $this->total_amount;
        $balance = max(0, round($total - $paid, 2));

        $status = self::PAYMENT_STATUS_UNPAID;
        if ($paid >= $total && $total > 0) {
            $status = self::PAYMENT_STATUS_PAID;
        } elseif ($paid > 0) {
            $status = self::PAYMENT_STATUS_PARTIAL;
        }

        $this->update([
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'payment_status' => $status,
        ]);
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_PAID => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            self::PAYMENT_STATUS_PARTIAL => 'bg-amber-100 text-amber-800 border border-amber-200',
            default => 'bg-rose-100 text-rose-800 border border-rose-200',
        };
    }

    public static function nextBillNumber(): string
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->bill_number, -4) + 1) : 1;

        return sprintf('PB-%s-%04d', $year, $next);
    }
}
