<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Recaptcha::enabled() && ! Recaptcha::verify($request->input('g-recaptcha-response'), $request->ip(), 'admin_login')) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
        }

        // Session-only auth: no long-lived "remember" cookies (5-year recaller).
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();

            if (! $admin->is_active) {
                Auth::guard('admin')->logout();

                // Generic message: confirming "valid credentials but inactive"
                // would let anyone probe which accounts exist and are disabled.
                return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
            }

            RateLimiter::clear('admin-login:'.Str::lower($credentials['email']).'|'.$request->ip());

            $admin->last_login_at = now();
            $admin->last_login_ip = $request->ip();
            $admin->save();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
