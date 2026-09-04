<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderStageAttachment extends Model
{
    protected $fillable = [
        'work_order_stage_id', 'type', 'file_path', 'original_name',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkOrderStage::class, 'work_order_stage_id');
    }
}