<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStage extends Model
{
    protected $fillable = [
        'service_id', 'name', 'description', 'sort_order',
        'requires_photo', 'min_photos', 'requires_pdf',
    ];

    protected function casts(): array
    {
        return [
            'requires_photo' => 'boolean',
            'requires_pdf' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
