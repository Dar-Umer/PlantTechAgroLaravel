<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerBookedService extends Notification
{
    use Queueable;

    public function __construct(protected WorkOrder $workOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New service booking: '.$this->workOrder->customer_name.' booked "'.$this->workOrder->service_name.'" ('.$this->workOrder->number.').',
            'type' => 'work_order',
            'work_order_number' => $this->workOrder->number,
            'work_order_id' => $this->workOrder->id,
            'url' => route('admin.work-orders.show', $this->workOrder, false),
        ];
    }
}