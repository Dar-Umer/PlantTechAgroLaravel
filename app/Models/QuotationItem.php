<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'product_id', 'name', 'unit', 'qty', 'rate',
        'discount', 'gst_rate', 'total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'rate' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function taxableAmount(): float
    {
        $base = ((float) $this->qty * (float) $this->rate) - (float) $this->discount;

        return max(0, $base);
    }

    public function taxAmount(): float
    {
        return round($this->taxableAmount() * ((float) $this->gst_rate / 100), 2);
    }

    public function lineTotal(): float
    {
        return round($this->taxableAmount() + $this->taxAmount(), 2);
    }
}
