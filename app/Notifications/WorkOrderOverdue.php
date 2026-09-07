<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Concerns\ConfigurableChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderOverdue extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Invoice $invoice) {}

    public function toMail(object $notifiable): MailMessage
    {
        $due = $this->invoice->due_date?->format('d M Y') ?? 'past due date';

        return (new MailMessage)
            ->subject('Invoice '.$this->invoice->number.' is overdue')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Invoice **'.$this->invoice->number.'** for '.$this->invoice->customer_name.' has passed its due date ('.$due.').')
            ->line('**Balance due:** ₹'.number_format($this->invoice->balanceDue(), 0))
            ->action('View Invoice', route('admin.invoices.show', $this->invoice));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Invoice overdue',
            'invoice_id' => $this->invoice->id,
            'number' => $this->invoice->number,
            'customer' => $this->invoice->customer_name,
            'amount_due' => (float) $this->invoice->balanceDue(),
        ];
    }
}