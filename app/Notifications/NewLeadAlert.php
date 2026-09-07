<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadAlert extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Lead $lead) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New lead: '.$this->lead->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new lead just submitted the website form.')
            ->line('**Name:** '.$this->lead->name)
            ->line('**Phone:** '.($this->lead->phone ?: '—'))
            ->line('**Service:** '.($this->lead->service?->name ?: '—'))
            ->action('View Lead', route('admin.leads.show', $this->lead));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New lead submitted',
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'phone' => $this->lead->phone,
            'service' => $this->lead->service?->name,
        ];
    }
}