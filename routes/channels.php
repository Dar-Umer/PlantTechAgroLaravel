<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// All active admins share one private channel for instant lead popups.
// Authenticated via the session; membership requires the admin guard.
Broadcast::channel('admin.leads', function () {
    $admin = Auth::guard('admin')->user();

    return $admin && $admin->is_active ? ['id' => $admin->id, 'name' => $admin->name] : false;
});
