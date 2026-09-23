<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Orchard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrchardController extends Controller
{
    public function index(Request $request)
    {
        $query = Orchard::query()->with('customer')->latest();

        if ($type = $request->query('type')) {
            if ($type === 'company') {
                $query->where('is_company_established', true);
            } elseif ($type === 'self') {
                $query->where('is_company_established', false);
            }
        }

        if ($customerId = $request->query('customer_id')) {
            if (is_numeric($customerId)) {
                $query->where('customer_id', (int) $customerId);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('orchard_id', 'like', "%{$escaped}%")
                    ->orWhere('name', 'like', "%{$escaped}%")
                    ->orWhere('address', 'like', "%{$escaped}%")
                    ->orWhereHas('customer', function ($cq) use ($escaped) {
                        $cq->where('name', 'like', "%{$escaped}%")
                            ->orWhere('orchardist_id', 'like', "%{$escaped}%")
                            ->orWhere('phone', 'like', "%{$escaped}%");
                    });
            });
        }

        $orchards = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Orchard::count(),
            'company' => Orchard::where('is_company_established', true)->count(),
            'self' => Orchard::where('is_company_established', false)->count(),
            'total_kanals' => round((float) Orchard::sum('area_kanals'), 1),
            'total_plants' => (int) Orchard::sum('tree_count'),
        ];

        return view('admin.orchards.index', compact('orchards', 'stats'));
    }

    public function create(Request $request)
    {
        $preselectCustomerId = $request->query('customer_id');
        $preselectCustomer = is_numeric($preselectCustomerId)
            ? Customer::whereKey((int) $preselectCustomerId)->first()
            : null;

        $customers = Customer::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'orchardist_id', 'area', 'address']);

        return view('admin.orchards.create', [
            'customers' => $customers,
            'preselectCustomer' => $preselectCustomer,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_kanals' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'tree_count' => ['required', 'integer', 'min:0', 'max:1000000'],
            'date_of_establishment' => ['required', 'date', 'before_or_equal:today'],
            'is_company_established' => ['boolean'],
            'variety_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'dormant', 'archived'])],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_company_established'] = (bool) ($request->boolean('is_company_established'));

        $orchard = Orchard::create($data);

        return redirect()->route('admin.orchards.show', $orchard)
            ->with('success', 'Orchard '.$orchard->orchard_id.' successfully registered.');
    }

    public function show(Orchard $orchard)
    {
        $orchard->loadMissing([
            'customer.workOrders',
            'establishmentWorkOrder',
            'workOrders' => function ($q) {
                $q->with(['agent:id,name', 'invoice:id,number,status,grand_total,amount_paid', 'stages']);
            },
        ]);

        return view('admin.orchards.show', compact('orchard'));
    }

    public function edit(Orchard $orchard)
    {
        $customers = Customer::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'orchardist_id']);

        return view('admin.orchards.edit', compact('orchard', 'customers'));
    }

    public function update(Request $request, Orchard $orchard)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_kanals' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'tree_count' => ['required', 'integer', 'min:0', 'max:1000000'],
            'date_of_establishment' => ['required', 'date', 'before_or_equal:today'],
            'is_company_established' => ['boolean'],
            'variety_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'dormant', 'archived'])],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_company_established'] = (bool) ($request->boolean('is_company_established'));

        $orchard->update($data);

        return redirect()->route('admin.orchards.show', $orchard)
            ->with('success', 'Orchard '.$orchard->orchard_id.' updated.');
    }

    public function destroy(Orchard $orchard)
    {
        $orchardId = $orchard->orchard_id;
        $orchard->delete();

        return redirect()->route('admin.orchards.index')
            ->with('success', 'Orchard '.$orchardId.' deleted.');
    }
}
