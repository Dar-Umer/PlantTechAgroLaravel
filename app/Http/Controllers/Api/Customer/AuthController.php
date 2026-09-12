<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\AppConfig;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        if (! $customer || ! $customer->isActive() || ! Hash::check($data['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'phone' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear('customer-login:'.Phone::digits($data['phone']).'|'.$request->ip());

        $customer->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $token = $customer->createToken('customer-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $customer->only(['id', 'name', 'phone', 'email', 'address', 'area', 'status']),
            'app_config' => AppConfig::toArray(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}