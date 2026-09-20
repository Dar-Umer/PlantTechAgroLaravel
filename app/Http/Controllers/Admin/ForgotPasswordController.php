<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Fail-closed like login, but with the same generic response either
        // way so bots learn nothing about the address or the captcha.
        if (Recaptcha::enabled() && ! Recaptcha::verify($request->input('g-recaptcha-response'), $request->ip(), 'admin_password')) {
            return back()->with('status', trans('passwords.sent'));
        }

        $status = Password::broker('admins')->sendResetLink($request->only('email'));

        return in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER])
            ? back()->with('status', trans('passwords.sent'))
            : back()->withErrors(['email' => __($status)]);
    }
}