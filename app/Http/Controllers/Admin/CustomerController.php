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

        return view('admin.customers.show', compact(
            'customer', 'lead', 'workOrders', 'invoices', 'servicesAvailed',
            'workOrdersTotal', 'workOrdersActive', 'workOrdersCompleted',
            'totalPaid', 'outstanding', 'overdueInvoices'
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
