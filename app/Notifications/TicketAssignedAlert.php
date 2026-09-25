<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedAlert extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Ticket $ticket) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ticket Assigned to You: ' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been assigned to resolve a farmer ticket.')
            ->line('**Ticket Number:** ' . $this->ticket->ticket_number)
            ->line('**Farmer:** ' . $this->ticket->customer?->name . ' (' . ($this->ticket->customer?->phone ?? '—') . ')')
            ->line('**Subject:** ' . $this->ticket->subject)
            ->line('**Priority:** ' . $this->ticket->priorityLabel())
            ->action('Open Ticket Workspace', route('admin.tickets.show', $this->ticket));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ticket assigned: ' . $this->ticket->ticket_number,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'farmer_name' => $this->ticket->customer?->name,
            'subject' => $this->ticket->subject,
            'priority' => $this->ticket->priority,
            'type' => 'ticket_assigned',
            'url' => route('admin.tickets.show', $this->ticket),
        ];
    }
}
