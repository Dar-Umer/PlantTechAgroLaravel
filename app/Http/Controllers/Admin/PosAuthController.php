<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PosAuthController extends Controller
{
    /**
     * Show dedicated POS terminal cashier login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.pos.terminal');
        }

        $posSettings = config('pos', []);

        return view('admin.pos.login', compact('posSettings'));
    }

    /**
     * Process cashier authentication on the POS terminal.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Please enter your Cashier ID, Email, or Mobile number.',
            'password.required' => 'Please enter your access password.',
        ]);

        $throttleKey = 'pos-login:' . Str::lower($request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 7)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => "Too many failed attempts. Please wait {$seconds} seconds before trying again."]);
        }

        $loginInput = trim((string) $request->input('login'));
        $password = (string) $request->input('password');

        // Lookup user by Email first, then by Phone digits
        $admin = null;

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $admin = Admin::whereRaw('LOWER(email) = ?', [Str::lower($loginInput)])->first();
        } else {
            // Check direct string or phone digits match
            $digits = preg_replace('/[^0-9]/', '', $loginInput);
            $admin = Admin::where('email', $loginInput)
                ->orWhere('phone', $loginInput)
                ->when(strlen($digits) >= 7, function ($q) use ($digits) {
                    $q->orWhere('phone', 'like', "%{$digits}%");
                })
                ->first();
        }

        if (! $admin) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'No cashier account found matching this Email or Phone number.']);
        }

        if (! $admin->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'This account is deactivated. Please contact your store administrator.']);
        }

        if (! Hash::check($password, $admin->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['password' => 'Incorrect password entered. Please check and try again.']);
        }

        // Login successfully
        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        RateLimiter::clear($throttleKey);

        $admin->last_login_at = now();
        $admin->last_login_ip = $request->ip();
        $admin->save();

        return redirect()->route('admin.pos.terminal')
            ->with('success', 'Logged in successfully. POS Terminal ready for ' . $admin->name);
    }

    /**
     * Log cashier out from POS session.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If on POS subdomain, redirect to /login
        if (str_starts_with($request->getHost(), 'pos.') || $request->getHost() === parse_url(config('pos.subdomain_url'), PHP_URL_HOST)) {
            return redirect('/login')->with('success', 'Logged out from POS terminal.');
        }

        return redirect()->route('pos.login')->with('success', 'Logged out from POS terminal.');
    }
}
