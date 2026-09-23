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
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'password' => ['required', 'string', 'min:6'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
            'orchard_name' => ['nullable', 'string', 'max:150'],
            'kanals' => ['nullable', 'numeric', 'min:0.1', 'max:1000'],
            'plants_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'variety' => ['nullable', 'string', 'max:100'],
        ]);

        $digits = Phone::digits($data['phone']);
        $existing = Customer::findByPhoneDigits($data['phone']);
        if ($existing) {
            throw ValidationException::withMessages([
                'phone' => 'An account with this mobile number already exists. Please log in.',
            ]);
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'phone' => $digits,
            'password' => $data['password'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'area' => $data['area'] ?? null,
            'status' => 'active',
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        if (! empty($data['orchard_name'])) {
            $customer->orchards()->create([
                'name' => $data['orchard_name'],
                'area_kanals' => $data['kanals'] ?? 1.0,
                'tree_count' => $data['plants_count'] ?? null,
                'variety_notes' => $data['variety'] ?? null,
                'address' => $data['address'] ?? $data['area'] ?? null,
                'is_company_established' => false,
                'date_of_establishment' => now()->toDateString(),
                'status' => 'active',
            ]);
        }

        $token = $customer->createToken('customer-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful! Welcome to Plant Tech Agro.',
            'token' => $token,
            'user' => array_merge(
                $customer->only(['id', 'orchardist_id', 'name', 'phone', 'email', 'address', 'area', 'status']),
                [
                    'orchards_count' => $customer->orchards()->count(),
                    'company_orchards_count' => $customer->companyOrchards()->count(),
                ]
            ),
            'app_config' => AppConfig::toArray(),
        ], 201);
    }

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
            'user' => array_merge(
                $customer->only(['id', 'orchardist_id', 'name', 'phone', 'email', 'address', 'area', 'status']),
                [
                    'orchards_count' => $customer->orchards()->count(),
                    'company_orchards_count' => $customer->companyOrchards()->count(),
                ]
            ),
            'app_config' => AppConfig::toArray(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}