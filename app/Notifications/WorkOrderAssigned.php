<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkOrderAssigned extends Notification
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New work order assigned',
            'work_order_id' => $this->workOrder->id,
            'number' => $this->workOrder->number,
            'customer' => $this->workOrder->customer_name,
            'service' => $this->workOrder->service_name,
            'stages' => $this->workOrder->stages()->count(),
        ];
    }
}
