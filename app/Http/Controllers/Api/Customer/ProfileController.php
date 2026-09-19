<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
            'app_config' => AppConfig::toArray(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'area' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($data);

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $this->userPayload($request->user()), // @phpstan-ignore-line
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->max(64)],
        ]);

        $customer = $request->user();

        if (! Hash::check($data['current_password'], $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $customer->password = Hash::make($data['password']);
        $customer->save();

        $customer->tokens()
            ->when($customer->currentAccessToken(), fn ($q, $token) => $q->where('id', '!=', $token->id))
            ->delete();

        return response()->json(['message' => 'Password changed.']);
    }

    private function userPayload(Customer $customer): array
    {
        return $customer->only(['id', 'name', 'phone', 'email', 'address', 'area', 'status']);
    }
}