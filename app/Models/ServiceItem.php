<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_id', 'name', 'description', 'image', 'sort_order',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
