<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Ticket $ticket, public string $previousStatus) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Status Update for Ticket #' . $this->ticket->ticket_number . ': ' . $this->ticket->statusLabel())
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('The status of your support ticket has been updated.')
            ->line('**Ticket Number:** ' . $this->ticket->ticket_number)
            ->line('**Subject:** ' . $this->ticket->subject)
            ->line('**New Status:** ' . $this->ticket->statusLabel())
            ->line('Open the PTA Mobile App if you have any follow-up questions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ticket #' . $this->ticket->ticket_number . ' is now ' . $this->ticket->statusLabel(),
            'message' => 'Status updated from ' . ucfirst(str_replace('_', ' ', $this->previousStatus)) . ' to ' . $this->ticket->statusLabel(),
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'status' => $this->ticket->status,
            'type' => 'ticket_status_change',
        ];
    }
}
