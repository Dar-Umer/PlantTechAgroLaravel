<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderStage extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'skipped' => 'Skipped',
    ];

    protected $fillable = [
        'work_order_id', 'service_stage_id', 'name', 'description', 'sort_order',
        'requires_photo', 'min_photos', 'requires_pdf', 'status', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'requires_photo' => 'boolean',
            'requires_pdf' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function templateStage(): BelongsTo
    {
        return $this->belongsTo(ServiceStage::class, 'service_stage_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(WorkOrderStageProduct::class);
    }
}
