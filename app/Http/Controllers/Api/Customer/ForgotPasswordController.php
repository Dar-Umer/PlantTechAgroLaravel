<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordOtp;
use App\Notifications\CustomerOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function requestOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        // Generic response to prevent phone-number enumeration.
        $generic = [
            'message' => 'If an account exists for this phone number, an OTP has been sent.',
            'expires_in_minutes' => (int) config('api.otp_expires_minutes', 10),
        ];

        if (! $customer) {
            return response()->json($generic);
        }

        // Invalidate any previous unconsumed OTPs so only the latest is valid.
        PasswordOtp::query()
            ->where('phone', $customer->phone)
            ->where('purpose', 'password_reset')
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        Cache::forget($this->attemptKey($customer->phone));

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordOtp::create([
            'phone' => $customer->phone,
            'code' => '', // legacy column kept NOT NULL; real secret lives in code_hash
            'code_hash' => Hash::make($code),
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes((int) config('api.otp_expires_minutes', 10)),
        ]);

        $this->deliver($customer, $code);

        // Double-gated debug helper: config flag AND local env only. Never in production.
        if (app()->environment('local') && config('mobile.echo_otp', config('api.echo_otp', false))) {
            $generic['debug_otp'] = $code;
        }

        return response()->json($generic);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        if (! $customer || ! $this->checkOtpAttempt($customer, $data['code'])) {
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
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->max(64)],
        ]);

        $customer = Customer::findByPhoneDigits($data['phone']);

        if (! $customer) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired OTP.',
            ]);
        }

        // Resolve + consume inside one locked transaction so two concurrent
        // submissions with the same code cannot both succeed.
        $consumed = DB::transaction(function () use ($customer, $data) {
            $otp = $this->resolveOtp($customer, $data['code'], true);

            if (! $otp) {
                return false;
            }

            $customer->password = Hash::make($data['password']);
            $customer->save();
            $customer->tokens()->delete();
            $otp->forceFill(['consumed_at' => now()])->save();

            return true;
        });

        if (! $consumed) {
            $this->recordFailedAttempt($customer->phone);
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired OTP.',
            ]);
        }

        Cache::forget($this->attemptKey($customer->phone));

        return response()->json(['message' => 'Password updated. You can now sign in.']);
    }

    protected function deliver(Customer $customer, string $code): void
    {
        $channel = config('automation.channel', 'both');

        if (in_array($channel, ['email', 'both'], true) && $customer->email) {
            Notification::route('mail', $customer->email)->notify(new CustomerOtp($code));
        }

        // Never log the OTP value itself — only that one was issued.
        Log::info('Customer OTP issued for phone ending in '.substr(preg_replace('/\D/', '', $customer->phone), -4));
    }

    private function attemptKey(string $phone): string
    {
        return 'otp_attempts:'.sha1($phone);
    }

    private function checkOtpAttempt(Customer $customer, string $code): bool
    {
        $key = $this->attemptKey($customer->phone);
        $attempts = (int) Cache::get($key, 0);
        $max = (int) config('api.otp_max_attempts', 5);

        if ($attempts >= $max) {
            // Lock out: invalidate outstanding OTPs to stop brute force.
            PasswordOtp::query()
                ->where('phone', $customer->phone)
                ->where('purpose', 'password_reset')
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            return false;
        }

        $otp = $this->resolveOtp($customer, $code);

        if (! $otp) {
            $this->recordFailedAttempt($customer->phone);

            return false;
        }

        return true;
    }

    private function recordFailedAttempt(string $phone): void
    {
        $key = $this->attemptKey($phone);
        $attempts = (int) Cache::get($key, 0) + 1;
        $max = (int) config('api.otp_max_attempts', 5);

        Cache::put($key, $attempts, now()->addMinutes((int) config('api.otp_expires_minutes', 10)));

        if ($attempts >= $max) {
            PasswordOtp::query()
                ->where('phone', $phone)
                ->where('purpose', 'password_reset')
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);
        }
    }

    private function resolveOtp(Customer $customer, string $code, bool $forUpdate = false): ?PasswordOtp
    {
        // Codes are bcrypt-hashed at rest, so candidates are compared in PHP.
        // Normally a single live row exists (older ones are consumed on issue).
        $query = PasswordOtp::query()
            ->where('phone', $customer->phone)
            ->where('purpose', 'password_reset')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->limit(5);

        $candidates = $forUpdate ? $query->lockForUpdate()->get() : $query->get();

        foreach ($candidates as $otp) {
            if ($otp->code_hash && Hash::check($code, $otp->code_hash)) {
                return $otp;
            }

            // Legacy plaintext rows predating the code_hash migration
            // (all expire within minutes of deploy).
            if (! $otp->code_hash && hash_equals((string) $otp->code, $code)) {
                return $otp;
            }
        }

        return null;
    }
}