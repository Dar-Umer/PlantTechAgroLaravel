<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'pending' => 'gray',
        'assigned' => 'blue',
        'in_progress' => 'yellow',
        'completed' => 'green',
        'cancelled' => 'red',
    ];

    protected $fillable = [
        'number', 'customer_id', 'customer_name', 'service_id', 'service_name',
        'assigned_agent_id', 'status', 'started_at', 'completed_at', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_agent_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorkOrderStage::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $workOrder) {
            if (empty($workOrder->number)) {
                $workOrder->number = static::nextNumber();
            }
        });
    }

    public static function nextNumber(): string
    {
        $seq = static::count() + 1;

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $number = sprintf('WO-%04d', $seq);

            if (! static::where('number', $number)->exists()) {
                return $number;
            }

            $seq++;
        }

        return 'WO-'.uniqid();
    }
}
