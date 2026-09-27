<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderCompletedNotification extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public WorkOrder $workOrder) {}

    public function getRendered(object $notifiable): array
    {
        return \App\Services\NotificationTemplateService::render('work_order_completed', [
            '{work_order_number}' => $this->workOrder->number,
            '{service_name}' => $this->workOrder->service_name,
            '{orchard_name}' => $this->workOrder->orchard?->name ?? 'Orchard',
            '{customer_name}' => $notifiable->name ?? $this->workOrder->customer_name ?? 'Farmer',
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rendered = $this->getRendered($notifiable);

        return (new MailMessage)
            ->subject('Work Order #' . $this->workOrder->number . ' Completed - Plant Tech Agro')
            ->greeting('Dear ' . ($notifiable->name ?? 'Farmer') . ',')
            ->line($rendered['body'])
            ->line('**Orchard:** ' . ($this->workOrder->orchard?->name ?? 'Orchard'))
            ->line('**Completion Date:** ' . ($this->workOrder->completed_at ? $this->workOrder->completed_at->format('M d, Y') : now()->format('M d, Y')))
            ->line('You can review full work order details, stage breakdown, and invoices in your Plant Tech Agro app.');
    }

    public function toArray(object $notifiable): array
    {
        $rendered = $this->getRendered($notifiable);

        return [
            'title' => $rendered['title'],
            'message' => $rendered['body'],
            'work_order_id' => $this->workOrder->id,
            'work_order_number' => $this->workOrder->number,
            'service_name' => $this->workOrder->service_name,
            'type' => 'work_order_completed',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        $rendered = $this->getRendered($notifiable);

        return [
            'title' => $rendered['title'],
            'body' => $rendered['body'],
            'data' => [
                'type' => 'work_order_completed',
                'work_order_id' => (string) $this->workOrder->id,
                'work_order_number' => (string) $this->workOrder->number,
                'service_name' => (string) $this->workOrder->service_name,
                'click_action' => 'OPEN_WORK_ORDER',
            ],
        ];
    }
}
