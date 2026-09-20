<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPES = [
        'in' => 'Stock In',
        'out' => 'Stock Out',
        'adjustment' => 'Adjustment',
    ];

    public const MANUAL_REF_PREFIX = 'MV-';

    protected $fillable = [
        'product_id', 'batch_id', 'type', 'quantity', 'stock_after', 'unit_cost',
        'supplier_id', 'reference', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'stock_after' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function stockBefore(): float
    {
        return round((float) $this->stock_after - (float) $this->quantity, 3);
    }

    public function withUnitCost(): bool
    {
        return $this->unit_cost !== null && (float) $this->unit_cost > 0;
    }

    public function movementValue(): ?float
    {
        if ($this->unit_cost === null) {
            return null;
        }

        return round((float) $this->unit_cost * abs((float) $this->quantity), 2);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
