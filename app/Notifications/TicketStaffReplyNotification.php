<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketStaffReplyNotification extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Ticket $ticket, public TicketMessage $ticketMessage) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PTA Support update on your query #' . $this->ticket->ticket_number)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Our technical support team has replied to your query.')
            ->line('**Ticket Number:** ' . $this->ticket->ticket_number)
            ->line('**Response:** ' . $this->ticketMessage->message)
            ->line('Please open the PTA Farmer Mobile App to view complete details, photos, or continue the conversation.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'PTA Support replied to #' . $this->ticket->ticket_number,
            'message' => Str::limit($this->ticketMessage->message, 150),
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_reply',
        ];
    }
}
