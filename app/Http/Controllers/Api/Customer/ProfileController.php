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

    public function ledger(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $from = $request->query('from')
            ? \Illuminate\Support\Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfYear()->startOfDay();

        $to = $request->query('to')
            ? \Illuminate\Support\Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        // Historical balances
        $prevInvoices = (float) $customer->invoices()
            ->whereNotIn('status', ['cancelled'])
            ->where('invoice_date', '<', $from->toDateString())
            ->sum('grand_total');

        $prevPayments = (float) \App\Models\Payment::whereHas('invoice', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)->whereNotIn('status', ['cancelled']);
            })
            ->where('paid_at', '<', $from->toDateString())
            ->sum('amount');

        $openingBalance = round($prevInvoices - $prevPayments, 2);

        // Period items
        $invoices = $customer->invoices()
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->get();

        $payments = \App\Models\Payment::whereHas('invoice', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)->whereNotIn('status', ['cancelled']);
            })
            ->whereDate('paid_at', '>=', $from->toDateString())
            ->whereDate('paid_at', '<=', $to->toDateString())
            ->with('invoice:id,number')
            ->get();

        $events = collect();

        foreach ($invoices as $inv) {
            $events->push([
                'date' => $inv->invoice_date,
                'created_at' => $inv->created_at,
                'type' => 'invoice',
                'reference' => $inv->number,
                'description' => 'Invoice ' . $inv->number,
                'debit' => (float) $inv->grand_total,
                'credit' => 0.0,
            ]);
        }

        foreach ($payments as $pmt) {
            $events->push([
                'date' => $pmt->paid_at ? \Illuminate\Support\Carbon::parse($pmt->paid_at)->toDateString() : $pmt->created_at?->toDateString(),
                'created_at' => $pmt->created_at,
                'type' => 'payment',
                'reference' => $pmt->reference ?? ('PAY-' . $pmt->id),
                'description' => 'Payment for ' . ($pmt->invoice?->number ?? 'Invoice') . ' via ' . strtoupper($pmt->method),
                'debit' => 0.0,
                'credit' => (float) $pmt->amount,
            ]);
        }

        $sorted = $events->sortBy(fn ($e) => $e['date'] . ' ' . ($e['created_at'] ?? ''))->values();

        $running = $openingBalance;
        $transactions = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($sorted as $item) {
            $running = round($running + $item['debit'] - $item['credit'], 2);
            $totalDebit += $item['debit'];
            $totalCredit += $item['credit'];

            $item['running_balance'] = $running;
            $transactions[] = $item;
        }

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'opening_balance' => $openingBalance,
            'closing_balance' => $running,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'transactions' => $transactions,
        ]);
    }

    private function userPayload(Customer $customer): array
    {
        return $customer->only(['id', 'name', 'phone', 'email', 'address', 'area', 'status']);
    }
}