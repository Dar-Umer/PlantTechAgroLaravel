<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordOtp;
use App\Notifications\CustomerOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function requestOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        if (! $customer) {
            throw ValidationException::withMessages([
                'phone' => 'No account found with this phone number.',
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordOtp::create([
            'phone' => $customer->phone,
            'code' => $code,
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes((int) config('api.otp_expires_minutes', 15)),
        ]);

        $this->deliver($customer, $code);

        $payload = [
            'message' => 'OTP sent successfully.',
            'expires_in_minutes' => (int) config('api.otp_expires_minutes', 15),
        ];

        if (config('mobile.echo_otp', config('api.echo_otp', false))) {
            $payload['debug_otp'] = $code;
        }

        return response()->json($payload);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        if (! $customer || ! $this->resolveOtp($customer, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired OTP.',
            ]);
        }

        return response()->json(['message' => 'OTP verified.']);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'max:64', 'confirmed'],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        if (! $customer) {
            throw ValidationException::withMessages([
                'phone' => 'No account found with this phone number.',
            ]);
        }

        $otp = $this->resolveOtp($customer, $data['code']);

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired OTP.',
            ]);
        }

        DB::transaction(function () use ($customer, $otp, $data) {
            $customer->password = Hash::make($data['password']);
            $customer->save();
            $customer->tokens()->delete();
            $otp->forceFill(['consumed_at' => now()])->save();
        });

        return response()->json(['message' => 'Password updated. You can now sign in.']);
    }

    protected function deliver(Customer $customer, string $code): void
    {
        $channel = config('automation.channel', 'both');

        if (in_array($channel, ['email', 'both'], true) && $customer->email) {
            Notification::route('mail', $customer->email)->notify(new CustomerOtp($code));
        }

        \Illuminate\Support\Facades\Log::info('Customer OTP issued for '.$customer->phone.': '.$code);
    }

    private function resolveOtp(Customer $customer, string $code): ?PasswordOtp
    {
        return PasswordOtp::query()
            ->where('phone', $customer->phone)
            ->where('code', $code)
            ->where('purpose', 'password_reset')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
    }
}