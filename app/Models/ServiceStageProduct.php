<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStageProduct extends Model
{
    protected $fillable = [
        'service_stage_id', 'product_id', 'quantity', 'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ServiceStage::class, 'service_stage_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
