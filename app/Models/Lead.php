<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'no_answer' => 'No Answer',
        'interested' => 'Interested',
        'converted' => 'Converted',
        'lost' => 'Lost',
    ];

    public const STATUS_COLORS = [
        'new' => 'blue',
        'contacted' => 'yellow',
        'no_answer' => 'gray',
        'interested' => 'purple',
        'converted' => 'green',
        'lost' => 'red',
    ];

    protected $fillable = [
        'name', 'phone', 'service_id', 'custom_fields', 'status',
        'notes', 'source', 'converted_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }
}
