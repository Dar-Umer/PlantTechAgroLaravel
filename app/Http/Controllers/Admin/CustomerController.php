<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('q'))) {
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
        $lead = $customer->lead;

        return view('admin.customers.show', compact('customer', 'lead'));
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
            ? ['required', 'string', 'min:6', 'max:64']
            : ['nullable', 'string', 'min:6', 'max:64'];

        return $request->validate($rules);
    }
}
