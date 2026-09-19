<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Polled by the admin layout every N seconds to pop up new leads.
     * Only unread NewLeadAlert notifications for the logged-in admin.
     */
    public function latest(Request $request)
    {
        $admin = $request->user('admin');

        $leads = $admin->unreadNotifications()
            ->where('type', \App\Notifications\NewLeadAlert::class)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($notification) => [
                'notification_id' => $notification->id,
                'lead_id' => $notification->data['lead_id'] ?? null,
                'name' => $notification->data['name'] ?? 'New lead',
                'phone' => $notification->data['phone'] ?? '',
                'service' => $notification->data['service'] ?? '',
                'arrived' => $notification->created_at?->diffForHumans(),
                'url' => isset($notification->data['lead_id'])
                    ? route('admin.leads.show', $notification->data['lead_id'])
                    : route('admin.leads.index'),
            ])
            ->values();

        return response()->json([
            'unread_count' => $admin->unreadNotifications()->count(),
            'popup_enabled' => (bool) config('automation.new_lead_popup_enabled', true),
            'poll_interval' => max(15, (int) config('automation.new_lead_popup_interval', 60)),
            'leads' => $leads,
        ]);
    }

    /**
     * Mark a single notification as read (used by the new-lead popup
     * so an acknowledged lead never pops up again).
     */
    public function read(Request $request, string $notification)
    {
        $item = $request->user('admin')->notifications()->whereKey($notification)->firstOrFail();

        if (! $item->read_at) {
            $item->markAsRead();
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function readAll(Request $request)
    {
        $request->user('admin')->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
