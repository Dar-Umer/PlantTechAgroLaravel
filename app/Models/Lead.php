<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'notes', 'source', 'converted_customer_id', 'last_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'last_reminder_sent_at' => 'datetime',
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

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class)->latest();
    }

    public function latestQuotation(): HasOne
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    /**
     * A converted lead is a historical record: read-only, never
     * re-convertible, never deletable, status locked.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }
}
