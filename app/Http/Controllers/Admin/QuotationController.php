<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageProduct;
use App\Services\QuotationNumberer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query()->with(['service', 'lead', 'customer', 'workOrder'])->latest();

        if ($status = $request->query('status')) {
            if (in_array($status, array_keys(Quotation::STATUSES), true)) {
                $query->where('status', $status);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $quotations = $query->paginate(15)->withQueryString();

        $statusCounts = Quotation::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.quotations.index', compact('quotations', 'statusCounts'));
    }

    public function create(Request $request)
    {
        $lead = null;
        if ($leadId = $request->query('lead_id')) {
            $lead = Lead::with('service')->find($leadId);
        }

        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit', 'rate', 'gst_rate']);

        // Default quotation terms from config or standard fallback
        $defaultTerms = config('quotation.terms', "1. Payment terms: 50% advance before project commencement, balance on completion.\n2. Quotation is valid for 15 days from the issue date.\n3. Weather conditions may affect drone spraying or field application schedule.");

        return view('admin.quotations.create', compact('lead', 'services', 'products', 'defaultTerms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['nullable', Rule::exists('leads', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{7,25}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'customer_area' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => ['required', Rule::in(array_keys(Quotation::STATUSES))],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $quotation = DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $discountTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;

            $itemsData = [];
            foreach ($data['items'] as $index => $item) {
                $qty = (float) $item['qty'];
                $rate = (float) $item['rate'];
                $discount = (float) ($item['discount'] ?? 0);
                $gstRate = (float) ($item['gst_rate'] ?? 0);

                $taxable = max(0, ($qty * $rate) - $discount);
                $tax = round($taxable * ($gstRate / 100), 2);
                $total = round($taxable + $tax, 2);

                $subtotal += $taxable;
                $discountTotal += $discount;
                $gstTotal += $tax;
                $grandTotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'],
                    'unit' => $item['unit'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'discount' => $discount,
                    'gst_rate' => $gstRate,
                    'total' => $total,
                    'sort_order' => $index,
                ];
            }

            $quotation = Quotation::create([
                'number' => QuotationNumberer::next(),
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'service_id' => $data['service_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_area' => $data['customer_area'] ?? null,
                'date' => $data['date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'gst_total' => $gstTotal,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_by' => $request->user('admin')->id ?? null,
            ]);

            foreach ($itemsData as $row) {
                $quotation->items()->create($row);
            }

            return $quotation;
        });

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->number} created successfully.");
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.product', 'lead', 'customer', 'service', 'workOrder', 'createdBy']);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->isApproved()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'Approved quotations are locked and cannot be edited.');
        }

        $quotation->load('items');
        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit', 'rate', 'gst_rate']);

        return view('admin.quotations.edit', compact('quotation', 'services', 'products'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->isApproved()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'Approved quotations are locked and cannot be edited.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{7,25}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'customer_area' => ['nullable', 'string', 'max:255'],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => ['required', Rule::in(array_keys(Quotation::STATUSES))],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($quotation, $data) {
            $subtotal = 0;
            $discountTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;

            $itemsData = [];
            foreach ($data['items'] as $index => $item) {
                $qty = (float) $item['qty'];
                $rate = (float) $item['rate'];
                $discount = (float) ($item['discount'] ?? 0);
                $gstRate = (float) ($item['gst_rate'] ?? 0);

                $taxable = max(0, ($qty * $rate) - $discount);
                $tax = round($taxable * ($gstRate / 100), 2);
                $total = round($taxable + $tax, 2);

                $subtotal += $taxable;
                $discountTotal += $discount;
                $gstTotal += $tax;
                $grandTotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'],
                    'unit' => $item['unit'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'discount' => $discount,
                    'gst_rate' => $gstRate,
                    'total' => $total,
                    'sort_order' => $index,
                ];
            }

            $quotation->update([
                'service_id' => $data['service_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_area' => $data['customer_area'] ?? null,
                'date' => $data['date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'gst_total' => $gstTotal,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            $quotation->items()->delete();
            foreach ($itemsData as $row) {
                $quotation->items()->create($row);
            }
        });

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->number} updated successfully.");
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->work_order_id) {
            return back()->with('error', 'Cannot delete a quotation linked to an active work order.');
        }

        $quotation->delete();

        return redirect()->route('admin.quotations.index')
            ->with('success', 'Quotation deleted.');
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['items.product', 'service', 'lead', 'customer']);

        return view('admin.quotations.print', [
            'quotation' => $quotation,
            'forPdf' => false,
        ]);
    }

    public function pdf(Quotation $quotation)
    {
        $quotation->load(['items.product', 'service', 'lead', 'customer']);

        $pdf = Pdf::loadView('admin.quotations.print', [
            'quotation' => $quotation,
            'forPdf' => true,
        ])->setPaper('a4');

        $filename = str_replace('/', '-', $quotation->number) . '-proforma-quotation.pdf';

        return $pdf->download($filename);
    }

    /**
     * "Approve & Start Work":
     * - Finds or creates Customer record.
     * - Converts Lead (if linked).
     * - Creates active Work Order (in_progress) with quoted items.
     * - Marks Quotation approved.
     */
    public function approveAndStartWork(Request $request, Quotation $quotation)
    {
        if (! $quotation->canApprove()) {
            return back()->with('error', 'This quotation has already been approved or linked to a work order.');
        }

        $quotation->load(['items', 'lead.service.stages', 'service.stages']);

        $workOrder = DB::transaction(function () use ($quotation, $request) {
            // 1. Resolve or create customer
            $customer = null;
            if ($quotation->customer_id) {
                $customer = Customer::find($quotation->customer_id);
            }

            if (! $customer) {
                $customer = Customer::findByPhoneDigits($quotation->customer_phone);
            }

            if (! $customer) {
                $customer = Customer::create([
                    'name' => $quotation->customer_name,
                    'phone' => $quotation->customer_phone,
                    'email' => $quotation->customer_email,
                    'address' => $quotation->customer_address,
                    'area' => $quotation->customer_area,
                    'password' => Str::random(12),
                    'status' => 'active',
                    'lead_id' => $quotation->lead_id,
                ]);
            }

            // 2. Link & convert lead if exists
            $lead = $quotation->lead;
            if ($lead && ! $lead->isConverted()) {
                $lead->update([
                    'status' => 'converted',
                    'converted_customer_id' => $customer->id,
                ]);
            }

            // 3. Resolve service
            $service = $quotation->service ?: ($lead?->service);
            $serviceName = $service?->name ?? 'Agricultural Service Execution';

            // 4. Create active Work Order
            $notes = trim(implode("\n\n", array_filter([
                "Created from Approved Quotation #{$quotation->number} (Grand Total: ₹" . number_format((float) $quotation->grand_total, 2) . ")",
                $quotation->notes ? "Quotation notes: {$quotation->notes}" : null,
                $lead?->notes ? "Lead notes: {$lead->notes}" : null,
            ])));

            $workOrder = WorkOrder::create([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'service_id' => $service?->id,
                'service_name' => $serviceName,
                'assigned_agent_id' => null,
                'status' => 'in_progress', // Active work order
                'started_at' => now(),
                'notes' => $notes !== '' ? $notes : null,
                'created_by' => $request->user('admin')->id ?? null,
            ]);

            // 5. Populate Work Order Stages & Stage Products
            if ($service && $service->stages->isNotEmpty()) {
                foreach ($service->stages->sortBy('sort_order') as $template) {
                    WorkOrderStage::create([
                        'work_order_id' => $workOrder->id,
                        'service_stage_id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'sort_order' => $template->sort_order,
                        'requires_photo' => $template->requires_photo,
                        'min_photos' => $template->min_photos,
                        'requires_pdf' => $template->requires_pdf,
                    ]);
                }
            }

            // Ensure there is at least one active stage to anchor materials
            $targetStage = $workOrder->stages()->first();
            if (! $targetStage) {
                $targetStage = WorkOrderStage::create([
                    'work_order_id' => $workOrder->id,
                    'service_stage_id' => null,
                    'name' => 'Service Execution',
                    'description' => 'Execution of approved quotation deliverables',
                    'sort_order' => 1,
                ]);
            }

            // Copy quotation items as stage products for operational tracking & invoicing
            foreach ($quotation->items as $item) {
                WorkOrderStageProduct::create([
                    'work_order_stage_id' => $targetStage->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'quantity' => (float) $item->qty,
                    'rate' => (float) $item->rate,
                    'gst_rate' => (float) $item->gst_rate,
                ]);
            }

            // 6. Update quotation to approved and link work order
            $quotation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'customer_id' => $customer->id,
                'work_order_id' => $workOrder->id,
            ]);

            return $workOrder;
        });

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', "Quotation {$quotation->number} approved! Lead converted and active Work Order {$workOrder->number} has been started.");
    }
}
