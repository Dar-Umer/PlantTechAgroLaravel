<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function readAll(Request $request)
    {
        $request->user('admin')->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
