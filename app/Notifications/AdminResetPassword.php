<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPassword extends Notification
{
    use Queueable;

    public function __construct(public string $token, public string $email) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ]);

        return (new MailMessage)
            ->subject('Reset your admin password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You are receiving this email because we received a request to reset the password for your admin account.')
            ->line('Please click the button below to choose a new password. This link will expire in 60 minutes.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, no further action is needed.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Password reset requested',
            'message' => 'A password reset link was sent to '.$this->email.'.',
        ];
    }
}