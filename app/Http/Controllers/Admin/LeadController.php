<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query()->with('service')->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($serviceId = $request->query('service_id')) {
            $query->where('service_id', $serviceId);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(15)->withQueryString();
        $services = Service::orderBy('name')->get(['id', 'name']);
        $statusCounts = Lead::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.leads.index', compact('leads', 'services', 'statusCounts'));
    }

    public function show(Lead $lead)
    {
        $lead->load('service', 'convertedCustomer');

        return view('admin.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $services = Service::orderBy('name')->get(['id', 'name']);

        return view('admin.leads.edit', compact('lead', 'services'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update($data);

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead updated.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
        ]);

        $lead->update($data);

        return back()->with('success', 'Lead status updated to "'.Lead::STATUSES[$data['status']].'".');
    }

    public function showConvert(Lead $lead)
    {
        if ($lead->status === 'converted') {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'This lead has already been converted to a customer.');
        }

        $address = $lead->custom_fields['address'] ?? null;
        $area = $lead->custom_fields['area'] ?? null;

        return view('admin.leads.convert', compact('lead', 'address', 'area'));
    }

    public function convert(Request $request, Lead $lead)
    {
        if ($lead->status === 'converted') {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'This lead has already been converted to a customer.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/', Rule::unique('customers', 'phone')],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'area' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:64', 'confirmed'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = DB::transaction(function () use ($data, $lead) {
            $customer = Customer::create($data + [
                'status' => 'active',
                'lead_id' => $lead->id,
            ]);

            $lead->update([
                'status' => 'converted',
                'converted_customer_id' => $customer->id,
            ]);

            return $customer;
        });

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Lead converted to customer. Login credentials: Phone '.$customer->phone.' with the password you set.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }
}
