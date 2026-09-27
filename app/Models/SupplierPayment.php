<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'supplier_id',
        'purchase_bill_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public const METHODS = [
        'bank_transfer' => 'Bank Transfer / NEFT / RTGS',
        'cash' => 'Cash',
        'cheque' => 'Cheque',
        'upi' => 'UPI / NetBanking',
        'other' => 'Other',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public static function nextPaymentNumber(): string
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->payment_number, -4) + 1) : 1;

        return sprintf('SPAY-%s-%04d', $year, $next);
    }
}
