<?php

namespace App\Notifications\Concerns;

trait ConfigurableChannel
{
    /**
     * Deliver on the channel chosen in the Automation settings:
     * database always, plus mail when the channel is set to email or both.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (in_array(config('automation.channel', 'both'), ['email', 'both'], true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}