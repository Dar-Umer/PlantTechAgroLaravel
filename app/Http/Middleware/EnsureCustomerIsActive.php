<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deactivating a customer must cut API access immediately — Sanctum
 * tokens live up to 30 days, so the login-time isActive() check alone
 * is not enough.
 */
class EnsureCustomerIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user();

        if ($customer && method_exists($customer, 'isActive') && ! $customer->isActive()) {
            $customer->tokens()->delete();

            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        return $next($request);
    }
}
