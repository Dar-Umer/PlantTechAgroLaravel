<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOtp extends Notification
{
    public function __construct(protected string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your one-time password (OTP)')
            ->greeting('Hello!')
            ->line('Your one-time password (OTP) is:')
            ->line('**'.$this->code.'**')
            ->line('This code expires in 15 minutes. Do not share it with anyone.');
    }
}