<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Register or refresh a Firebase device token for the authenticated customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'device_id' => ['nullable', 'string', 'max:100'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        // If the token was previously registered to another user or guest, reassign it
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'tokenable_type' => get_class($user),
                'tokenable_id' => $user->getKey(),
                'platform' => $validated['platform'] ?? 'android',
                'device_id' => $validated['device_id'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'last_active_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Device token registered successfully for push notifications.',
            'device_token' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
                'device_name' => $deviceToken->device_name,
                'last_active_at' => $deviceToken->last_active_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Remove a device token (e.g. on logout).
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = $request->user();

        $deleted = $user->deviceTokens()
            ->where('token', $validated['token'])
            ->delete();

        return response()->json([
            'message' => $deleted ? 'Device token unregistered successfully.' : 'Token not found.',
        ]);
    }
}
