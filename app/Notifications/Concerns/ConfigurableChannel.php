<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\FcmChannel;
use App\Services\FirebaseService;

trait ConfigurableChannel
{
    /**
     * Deliver on the channel chosen in the Automation settings:
     * database always, mail when enabled, plus Firebase Push (FCM) when configured.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (in_array(config('automation.channel', 'both'), ['email', 'both'], true)) {
            $channels[] = 'mail';
        }

        try {
            if (app(FirebaseService::class)->isEnabled()) {
                $channels[] = FcmChannel::class;
            }
        } catch (\Throwable $e) {
            // Gracefully ignore if container or service encounters any issue
        }

        return $channels;
    }
}