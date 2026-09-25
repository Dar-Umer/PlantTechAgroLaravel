<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketCustomerReplyAlert extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Ticket $ticket, public TicketMessage $ticketMessage) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Farmer Replied to Ticket: ' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Farmer ' . $this->ticket->customer?->name . ' sent a reply on ticket #' . $this->ticket->ticket_number . '.')
            ->line('**Message:** ' . Str::limit($this->ticketMessage->message, 150))
            ->action('View & Respond', route('admin.tickets.show', $this->ticket));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reply on ticket ' . $this->ticket->ticket_number,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'farmer_name' => $this->ticket->customer?->name,
            'snippet' => Str::limit($this->ticketMessage->message, 100),
            'type' => 'ticket_farmer_reply',
            'url' => route('admin.tickets.show', $this->ticket),
        ];
    }
}
