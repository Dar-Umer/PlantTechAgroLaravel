<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderStageProduct extends Model
{
    protected $fillable = [
        'work_order_stage_id', 'product_id', 'name', 'unit', 'quantity', 'rate', 'gst_rate',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'rate' => 'decimal:2',
            'gst_rate' => 'decimal:2',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkOrderStage::class, 'work_order_stage_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotal(): float
    {
        return round((float) $this->quantity * (float) $this->rate, 2);
    }
}
