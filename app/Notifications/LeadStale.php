<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadStale extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Lead $lead) {}

    public function toMail(object $notifiable): MailMessage
    {
        $days = max(1, (int) config('automation.lead_stale_days', 3));

        return (new MailMessage)
            ->subject('Lead '.$this->lead->name.' has not been contacted')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('The lead **'.$this->lead->name.'** has not been touched for more than '.$days.' days.')
            ->line('**Phone:** '.($this->lead->phone ?: '—'))
            ->line('**Status:** '.(\App\Models\Lead::STATUSES[$this->lead->status] ?? $this->lead->status))
            ->action('View Lead', route('admin.leads.show', $this->lead));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Lead needs follow-up',
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'phone' => $this->lead->phone,
            'status' => $this->lead->status,
        ];
    }
}