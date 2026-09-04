<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'image', 'unit', 'rate', 'gst_rate',
        'stock_qty', 'low_stock_threshold', 'supplier_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'stock_qty' => 'decimal:3',
            'low_stock_threshold' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function stageTemplates()
    {
        return $this->hasMany(ServiceStageProduct::class);
    }

    public function isLowStock(): bool
    {
        return $this->low_stock_threshold > 0
            && bccomp((string) $this->stock_qty, (string) $this->low_stock_threshold, 3) <= 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
