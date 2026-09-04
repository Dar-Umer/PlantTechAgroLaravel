<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = [
        'cash' => 'Cash',
        'upi' => 'UPI',
        'bank' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ];

    protected $fillable = [
        'invoice_id', 'amount', 'method', 'paid_at', 'reference', 'note', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'received_by');
    }
}
