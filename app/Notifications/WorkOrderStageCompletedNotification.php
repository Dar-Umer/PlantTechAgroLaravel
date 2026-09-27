<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderStageCompletedNotification extends Notification
{
    use Queueable, ConfigurableChannel;

    public WorkOrder $workOrder;

    public function __construct(
        public WorkOrderStage $stage,
        public ?string $notes = null,
        public ?string $photoUrl = null
    ) {
        $this->workOrder = $stage->workOrder ?? WorkOrder::findOrFail($stage->work_order_id);
    }

    public function getFormattedTitle(object $notifiable): string
    {
        $raw = $this->stage->notification_title;
        if (empty($raw)) {
            $defaultTpl = \App\Services\NotificationTemplateService::get('work_order_stage_completed');
            $raw = $defaultTpl['title'] ?? 'Stage Completed: {stage_name}';
        }

        return $this->interpolate($raw, $notifiable);
    }

    public function getFormattedBody(object $notifiable): string
    {
        $raw = $this->stage->notification_body;
        if (empty($raw)) {
            $defaultTpl = \App\Services\NotificationTemplateService::get('work_order_stage_completed');
            $raw = $defaultTpl['body'] ?? "Stage '{stage_name}' for work order #{work_order_number} has been completed successfully.";
        }

        $msg = $this->interpolate($raw, $notifiable);
        if ($this->notes && ! empty($this->stage->notification_body)) {
            $msg .= ' ' . $this->notes;
        }

        return $msg;
    }

    protected function interpolate(string $text, object $notifiable): string
    {
        $replacements = [
            '{stage_name}' => $this->stage->name,
            '{work_order_number}' => $this->workOrder->number ?? '',
            '{service_name}' => $this->workOrder->service_name ?? '',
            '{orchard_name}' => $this->workOrder->orchard?->name ?? 'Orchard',
            '{customer_name}' => $notifiable->name ?? $this->workOrder->customer_name ?? 'Farmer',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->getFormattedTitle($notifiable);
        $body = $this->getFormattedBody($notifiable);

        $mail = (new MailMessage)
            ->subject('Update on Work Order #' . $this->workOrder->number . ': ' . $title)
            ->greeting('Dear ' . ($notifiable->name ?? 'Farmer') . ',')
            ->line($body)
            ->line('**Work Order:** #' . $this->workOrder->number)
            ->line('**Service:** ' . $this->workOrder->service_name)
            ->line('**Stage:** ' . $this->stage->name);

        if ($this->notes) {
            $mail->line('**Execution Notes:** ' . $this->notes);
        }

        $mail->line('You can view stage progress and uploaded field photos anytime in the Plant Tech Agro Mobile App.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->getFormattedTitle($notifiable),
            'message' => $this->getFormattedBody($notifiable),
            'work_order_id' => $this->workOrder->id,
            'work_order_number' => $this->workOrder->number,
            'stage_id' => $this->stage->id,
            'stage_name' => $this->stage->name,
            'photo_url' => $this->photoUrl,
            'type' => 'work_order_stage_completed',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->getFormattedTitle($notifiable),
            'body' => $this->getFormattedBody($notifiable),
            'image' => $this->photoUrl,
            'data' => [
                'type' => 'work_order_stage_completed',
                'work_order_id' => (string) $this->workOrder->id,
                'work_order_number' => (string) $this->workOrder->number,
                'stage_id' => (string) $this->stage->id,
                'stage_name' => (string) $this->stage->name,
                'click_action' => 'OPEN_WORK_ORDER',
            ],
        ];
    }
}
