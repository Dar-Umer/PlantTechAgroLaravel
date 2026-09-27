<?php

namespace App\Notifications\Channels;

use App\Services\FirebaseService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    public function __construct(protected FirebaseService $firebase) {}

    /**
     * Send the given notification via FCM.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->firebase->isEnabled()) {
            return;
        }

        // Get FCM device tokens from notifiable
        $tokens = [];
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $tokens = $notifiable->routeNotificationFor('fcm', $notification);
        }
        if (empty($tokens) && method_exists($notifiable, 'routeNotificationForFcm')) {
            $tokens = $notifiable->routeNotificationForFcm();
        }

        if (empty($tokens)) {
            return;
        }

        $tokens = is_array($tokens) ? $tokens : [$tokens];
        $tokens = array_filter($tokens);

        if (empty($tokens)) {
            return;
        }

        // Format message payload
        $title = 'Plant Tech Agro Update';
        $body = 'You have a new notification.';
        $data = [];
        $imageUrl = null;

        if (method_exists($notification, 'toFcm')) {
            $fcmData = $notification->toFcm($notifiable);
            if (is_array($fcmData)) {
                $title = $fcmData['title'] ?? $title;
                $body = $fcmData['body'] ?? $fcmData['message'] ?? $body;
                $data = $fcmData['data'] ?? [];
                $imageUrl = $fcmData['image'] ?? null;
            }
        } elseif (method_exists($notification, 'toArray')) {
            $arrayData = $notification->toArray($notifiable);
            if (is_array($arrayData)) {
                $title = $arrayData['title'] ?? $title;
                $body = $arrayData['message'] ?? $arrayData['body'] ?? $body;
                $data = $arrayData;
            }
        }

        try {
            $this->firebase->sendToTokens($tokens, $title, $body, $data, $imageUrl);
        } catch (\Throwable $e) {
            Log::error('FcmChannel: Error delivering notification via FCM: ' . $e->getMessage());
        }
    }
}
