<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Service;
use App\Services\WorkOrderCreator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query()->with('service')->latest();

        if ($status = $request->query('status')) {
            abort_unless(in_array($status, array_keys(Lead::STATUSES ?? ['new' => 1, 'contacted' => 1, 'no_answer' => 1, 'interested' => 1, 'converted' => 1, 'closed' => 1]), true), 422, 'Invalid status filter.');
            $query->where('status', $status);
        }

        if ($serviceId = $request->query('service_id')) {
            abort_unless(is_numeric($serviceId), 422, 'Invalid service filter.');
            $query->where('service_id', (int) $serviceId);
        }

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(15)->withQueryString();
        $services = Service::orderBy('name')->get(['id', 'name']);

        // Leads whose phone already belongs to a customer: Convert is
        // replaced by a direct Work Order handoff. One lookup per row.
        $leadsWithCustomer = [];
        foreach ($leads as $lead) {
            if ($lead->isConverted()) {
                continue;
            }
            $match = Customer::findByPhoneDigits($lead->phone);
            if ($match) {
                $leadsWithCustomer[$lead->id] = $match->id;
            }
        }
        $statusCounts = Lead::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.leads.index', compact('leads', 'services', 'statusCounts', 'leadsWithCustomer'));
    }

    public function show(Lead $lead)
    {
        $lead->load('service', 'convertedCustomer', 'quotations.workOrder');

        $existingCustomer = $lead->isConverted()
            ? $lead->convertedCustomer
            : Customer::findByPhoneDigits($lead->phone);

        return view('admin.leads.show', compact('lead', 'existingCustomer'));
    }

    public function edit(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'Converted leads are read-only. View the linked customer instead.');
        }

        $services = Service::orderBy('name')->get(['id', 'name']);

        return view('admin.leads.edit', compact('lead', 'services'));
    }

    public function update(Request $request, Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'Converted leads are read-only and cannot be edited.');
        }

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
        if ($lead->isConverted()) {
            return back()->with('error', 'Converted leads are locked and their call status cannot be changed.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
        ]);

        $lead->update($data);

        return back()->with('success', 'Lead status updated to "'.Lead::STATUSES[$data['status']].'".');
    }

    public function showConvert(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'This lead has already been converted to a customer.');
        }

        $address = $lead->custom_fields['address'] ?? null;
        $area = $lead->custom_fields['area'] ?? null;
        $service = $lead->service;
        $serviceUsable = $service && $service->is_active;
        $existingCustomer = Customer::findByPhoneDigits($lead->phone);

        return view('admin.leads.convert', compact('lead', 'address', 'area', 'service', 'serviceUsable', 'existingCustomer'));
    }

    public function convert(Request $request, Lead $lead)
    {
        if ($lead->isConverted()) {
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

        try {
            [$customer, $workOrder] = DB::transaction(function () use ($data, $lead, $request) {
                $customer = Customer::create($data + [
                    'status' => 'active',
                    'lead_id' => $lead->id,
                ]);

                $workOrder = WorkOrderCreator::fromConversion(
                    $lead->refresh(),
                    $customer,
                    $request->user('admin')->id ?? null
                );

                $lead->update([
                    'status' => 'converted',
                    'converted_customer_id' => $customer->id,
                ]);

                return [$customer, $workOrder];
            });
        } catch (\RuntimeException $e) {
            return back()
                ->with('error', 'Conversion blocked: '.$e->getMessage())
                ->withInput();
        }

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Lead converted to customer '.$customer->name.' (login phone '.$customer->phone.'). Pending work order '.$workOrder->number.' opened — no stock deducted.');
    }

    /**
     * Handoff for leads whose phone already belongs to a customer:
     * lock the lead as converted and jump to a prefilled work-order form.
     */
    public function createWorkOrder(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'This lead has already been converted.');
        }

        $customer = Customer::findByPhoneDigits($lead->phone);

        if (! $customer) {
            return back()->with('error', 'No matching customer found for this number anymore.');
        }

        $service = $lead->service;

        if (! $service || ! $service->is_active) {
            return back()->with('error', 'This lead has no active service, so no work order can be opened.');
        }

        $lead->update([
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
        ]);

        return redirect()->route('admin.work-orders.create', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
        ])->with('success', "Lead linked to existing customer {$customer->name}. Complete the work order below — stock will be allocated on creation.");
    }

    public function destroy(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.show', $lead)
                ->with('error', 'Converted leads cannot be deleted. They are kept as a record of the customer source.');
        }

        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }
}
