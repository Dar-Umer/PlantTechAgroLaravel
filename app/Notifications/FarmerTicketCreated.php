<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FarmerTicketCreated extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Ticket $ticket) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Farmer Ticket: ' . $this->ticket->ticket_number . ' - ' . $this->ticket->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A farmer has submitted a new support ticket.')
            ->line('**Ticket Number:** ' . $this->ticket->ticket_number)
            ->line('**Farmer:** ' . $this->ticket->customer?->name . ' (' . ($this->ticket->customer?->phone ?? '—') . ')')
            ->line('**Category:** ' . $this->ticket->categoryLabel())
            ->line('**Priority:** ' . $this->ticket->priorityLabel())
            ->line('**Subject:** ' . $this->ticket->subject)
            ->action('View & Assign Ticket', route('admin.tickets.show', $this->ticket));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New ticket: ' . $this->ticket->ticket_number,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'farmer_name' => $this->ticket->customer?->name,
            'farmer_phone' => $this->ticket->customer?->phone,
            'subject' => $this->ticket->subject,
            'category' => $this->ticket->category,
            'priority' => $this->ticket->priority,
            'creation_mode' => $this->ticket->creation_mode,
            'type' => 'ticket_created',
            'url' => route('admin.tickets.show', $this->ticket),
        ];
    }
}
