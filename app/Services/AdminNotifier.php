<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Notifications\Notification;

class AdminNotifier
{
    /** Send a notification to every active admin (respects the channel via the notification itself). */
    public static function send(Notification $notification): void
    {
        Admin::where('is_active', true)->get()->each(function (Admin $admin) use ($notification) {
            $admin->notify($notification);
        });
    }
}