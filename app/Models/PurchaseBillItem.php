<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_bill_id',
        'product_id',
        'batch_id',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'quantity',
        'unit_cost',
        'selling_price',
        'tax_percent',
        'tax_amount',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'mfg_date' => 'date',
            'expiry_date' => 'date',
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
