<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->latest();

        if ($status = $request->query('status')) {
            abort_unless(in_array($status, ['active', 'inactive'], true), 422, 'Invalid status filter.');
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();

        return view('admin.customers.index', compact('customers', 'totalCustomers', 'activeCustomers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Customer::create($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        $customer->loadMissing('lead');

        $workOrders = $customer->workOrders()
            ->with(['service:id,name', 'agent:id,name', 'invoice:id,number,status,grand_total,amount_paid'])
            ->latest()
            ->paginate(6, ['*'], 'work_orders');

        $invoices = $customer->invoices()
            ->with(['workOrder:id,number', 'payments'])
            ->latest()
            ->limit(5)
            ->get();

        $servicesAvailed = $customer->workOrders()
            ->selectRaw('service_id, service_name, COUNT(*) as total, MAX(created_at) as last_booked')
            ->whereNotNull('service_name')
            ->groupBy('service_id', 'service_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->service_name,
                'total' => (int) $row->total,
                'last_booked' => $row->last_booked ? \Illuminate\Support\Carbon::parse($row->last_booked)->format('d M Y') : null,
            ]);

        $workOrdersTotal = $customer->workOrders()->count();
        $workOrdersActive = $customer->workOrders()->whereIn('status', ['pending', 'assigned', 'in_progress'])->count();
        $workOrdersCompleted = $customer->workOrders()->where('status', 'completed')->count();

        $totalPaid = (float) $customer->invoices()->sum('amount_paid');
        $outstanding = (float) $customer->invoices()
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum(DB::raw('grand_total - amount_paid'));
        $overdueInvoices = $customer->invoices()->where('status', 'overdue')->count();

        $lead = $customer->lead;
        $tickets = $customer->tickets()->with('assignedStaff')->latest()->limit(5)->get();

        return view('admin.customers.show', compact(
            'customer', 'lead', 'workOrders', 'invoices', 'servicesAvailed',
            'workOrdersTotal', 'workOrdersActive', 'workOrdersCompleted',
            'totalPaid', 'outstanding', 'overdueInvoices', 'tickets'
        ));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validated($request, $customer->id, false);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $customer->update($data);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }

    public function ledger(Customer $customer, Request $request)
    {
        $statement = $this->buildLedgerStatement($customer, $request);

        return view('admin.customers.ledger', array_merge(['customer' => $customer], $statement));
    }

    public function ledgerPdf(Customer $customer, Request $request)
    {
        $statement = $this->buildLedgerStatement($customer, $request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.customers.ledger_print', array_merge(['customer' => $customer], $statement));

        return $pdf->download("ledger_{$customer->id}_{$statement['from']->format('Ymd')}_{$statement['to']->format('Ymd')}.pdf");
    }

    public function ledgerCsv(Customer $customer, Request $request)
    {
        $statement = $this->buildLedgerStatement($customer, $request);

        $filename = "ledger_{$customer->id}_{$statement['from']->format('Ymd')}_{$statement['to']->format('Ymd')}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($statement) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Reference', 'Description', 'Debit (₹)', 'Credit (₹)', 'Running Balance (₹)']);
            fputcsv($out, [$statement['from']->format('Y-m-d'), 'OPENING', '-', 'Opening Balance', '', '', number_format($statement['openingBalance'], 2, '.', '')]);

            foreach ($statement['transactions'] as $tx) {
                fputcsv($out, [
                    $tx['date'],
                    strtoupper($tx['type']),
                    $tx['reference'],
                    $tx['description'],
                    $tx['debit'] > 0 ? number_format($tx['debit'], 2, '.', '') : '',
                    $tx['credit'] > 0 ? number_format($tx['credit'], 2, '.', '') : '',
                    number_format($tx['running_balance'], 2, '.', ''),
                ]);
            }
            fputcsv($out, [$statement['to']->format('Y-m-d'), 'CLOSING', '-', 'Closing Outstanding', '', '', number_format($statement['closingBalance'], 2, '.', '')]);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildLedgerStatement(Customer $customer, Request $request): array
    {
        $from = $request->query('from')
            ? \Illuminate\Support\Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfYear()->startOfDay();

        $to = $request->query('to')
            ? \Illuminate\Support\Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        // Historical prior to $from:
        // Debits: Non-cancelled Invoices
        $prevInvoices = (float) $customer->invoices()
            ->whereNotIn('status', ['cancelled'])
            ->where('invoice_date', '<', $from->toDateString())
            ->sum('grand_total');

        // Credits: Payments for invoices before $from
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

        // Sort chronologically
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

        return [
            'from' => $from,
            'to' => $to,
            'openingBalance' => $openingBalance,
            'closingBalance' => $running,
            'totalDebit' => round($totalDebit, 2),
            'totalCredit' => round($totalCredit, 2),
            'transactions' => $transactions,
        ];
    }

    private function validated(Request $request, ?int $ignoreId = null, bool $passwordRequired = true): array
    {
        $phoneRule = ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'];
        if ($ignoreId) {
            $phoneRule[] = Rule::unique('customers', 'phone')->ignore($ignoreId);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => $phoneRule,
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'area' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ];

        $rules['password'] = $passwordRequired
            ? ['required', 'string', 'max:64', Password::min(8)->letters()->numbers()]
            : ['nullable', 'string', 'max:64', Password::min(8)->letters()->numbers()];

        return $request->validate($rules);
    }
}
