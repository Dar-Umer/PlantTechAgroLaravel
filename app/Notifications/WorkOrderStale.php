<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderStale extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public WorkOrder $workOrder) {}

    public function toMail(object $notifiable): MailMessage
    {
        $days = max(1, (int) config('automation.work_order_stale_days', 7));

        return (new MailMessage)
            ->subject('Work order '.$this->workOrder->number.' needs attention')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Work order **'.$this->workOrder->number.'** — '.$this->workOrder->customer_name.' ('.$this->workOrder->service_name.') — has had no activity for more than '.$days.' days.')
            ->action('View Work Order', route('admin.work-orders.show', $this->workOrder));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Work order needs attention',
            'work_order_id' => $this->workOrder->id,
            'number' => $this->workOrder->number,
            'customer' => $this->workOrder->customer_name,
            'service' => $this->workOrder->service_name,
        ];
    }
}