<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageAttachment;
use App\Models\WorkOrderStageProduct;
use App\Notifications\WorkOrderAssigned;
use App\Services\InvoiceNumberer;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::query()->with(['agent', 'invoice'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $workOrders = $query->paginate(15)->withQueryString();

        return view('admin.work_orders.index', [
            'workOrders' => $workOrders,
            'counts' => WorkOrder::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create()
    {
        return view('admin.work_orders.create', [
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']),
            'services' => Service::active()->with(['stages.products.product'])->orderBy('sort_order')->get(),
            'agents' => Admin::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'assigned_agent_id' => ['nullable', 'exists:admins,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $service = Service::with('stages.products.product')->findOrFail($data['service_id']);

        $workOrder = DB::transaction(function () use ($data, $customer, $service, $request) {
            $workOrder = WorkOrder::create([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'assigned_agent_id' => $data['assigned_agent_id'] ?? null,
                'status' => ($data['assigned_agent_id'] ?? null) ? 'assigned' : 'pending',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user('admin')->id ?? null,
            ]);

            foreach ($service->stages->sortBy('sort_order') as $template) {
                $stage = WorkOrderStage::create([
                    'work_order_id' => $workOrder->id,
                    'service_stage_id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'sort_order' => $template->sort_order,
                    'requires_photo' => $template->requires_photo,
                    'min_photos' => $template->min_photos,
                    'requires_pdf' => $template->requires_pdf,
                ]);

                foreach ($template->products as $templateProduct) {
                    $product = $templateProduct->product;

                    if (! $product) {
                        continue;
                    }

                    WorkOrderStageProduct::create([
                        'work_order_stage_id' => $stage->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'quantity' => (float) $templateProduct->quantity,
                        'rate' => (float) $product->rate,
                        'gst_rate' => (float) $product->gst_rate,
                    ]);

                    // Planned materials are allocated to the job immediately.
                    StockService::record($product, 'out', (float) $templateProduct->quantity, $workOrder->number, 'Allocated to '.$template->name, null, null, $request->user('admin')->id ?? null);
                }
            }

            return $workOrder;
        });

        if ($workOrder->assigned_agent_id) {
            $workOrder->agent?->notify(new WorkOrderAssigned($workOrder));
        }

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order '.$workOrder->number.' created with '.$workOrder->stages()->count().' stage(s).');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['stages.products.product', 'stages.attachments', 'agent', 'createdBy', 'customer', 'service.stages', 'invoice']);

        $agents = Admin::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'unit', 'rate', 'gst_rate', 'stock_qty']);

        $materialTotal = 0.0;
        $materialGst = 0.0;
        foreach ($workOrder->stages as $stage) {
            foreach ($stage->products as $row) {
                $materialTotal += $row->lineTotal();
                $materialGst += round($row->lineTotal() * (float) $row->gst_rate / 100, 2);
            }
        }

        return view('admin.work_orders.show', [
            'workOrder' => $workOrder,
            'agents' => $agents,
            'products' => $products,
            'materialTotal' => round($materialTotal, 2),
            'materialGst' => round($materialGst, 2),
        ]);
    }

    public function assign(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->isCancelled() || $workOrder->status === 'completed') {
            return back()->with('error', 'This work order can no longer be assigned.');
        }

        $data = $request->validate([
            'assigned_agent_id' => ['required', 'exists:admins,id'],
        ]);

        $agent = Admin::findOrFail($data['assigned_agent_id']);

        $workOrder->update([
            'assigned_agent_id' => $agent->id,
            'status' => $workOrder->status === 'pending' ? 'assigned' : $workOrder->status,
        ]);

        $agent->notify(new WorkOrderAssigned($workOrder));

        return back()->with('success', 'Work order assigned to '.$agent->name.'.');
    }

    public function cancel(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->status === 'completed') {
            return back()->with('error', 'Completed work orders cannot be cancelled.');
        }

        $workOrder->update(['status' => 'cancelled']);

        return redirect()->route('admin.work-orders.show', $workOrder)->with('success', 'Work order cancelled.');
    }

    public function completeStage(Request $request, WorkOrder $workOrder, WorkOrderStage $stage)
    {
        if ($stage->work_order_id !== $workOrder->id || $workOrder->isCancelled()) {
            return back()->with('error', 'Invalid stage.');
        }

        $rules = [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($stage->requires_photo) {
            $rules['photos'] = ['required', 'array', 'min:'.$stage->min_photos];
            $rules['photos.*'] = ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'];
        }

        if ($stage->requires_pdf) {
            $rules['pdf'] = ['required', 'file', 'mimes:pdf', 'max:10240'];
        }

        $data = $request->validate($rules);

        $stage->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $data['notes'] ?? $stage->notes,
        ]);

        if ($stage->requires_photo && $request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $stage->attachments()->create([
                    'type' => 'photo',
                    'file_path' => $photo->store('work-orders/stages', 'public'),
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }
        }

        if ($stage->requires_pdf && $request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $stage->attachments()->create([
                'type' => 'pdf',
                'file_path' => $pdf->store('work-orders/stages', 'public'),
                'original_name' => $pdf->getClientOriginalName(),
            ]);
        }

        $this->refreshWorkOrderStatus($workOrder);

        return back()->with('success', 'Stage "'.$stage->name.'" marked as completed.');
    }

    public function destroyAttachment(WorkOrder $workOrder, WorkOrderStage $stage, WorkOrderStageAttachment $attachment)
    {
        if ($attachment->work_order_stage_id !== $stage->id || $stage->work_order_id !== $workOrder->id) {
            return back()->with('error', 'Invalid attachment.');
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    public function skipStage(Request $request, WorkOrder $workOrder, WorkOrderStage $stage)
    {
        if ($stage->work_order_id !== $workOrder->id || $workOrder->isCancelled()) {
            return back()->with('error', 'Invalid stage.');
        }

        $stage->update(['status' => 'skipped']);

        $this->refreshWorkOrderStatus($workOrder);

        return back()->with('success', 'Stage "'.$stage->name.'" skipped.');
    }

    public function addStageProduct(Request $request, WorkOrder $workOrder, WorkOrderStage $stage)
    {
        if ($stage->work_order_id !== $workOrder->id || $workOrder->isCancelled()) {
            return back()->with('error', 'Invalid stage.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $row = WorkOrderStageProduct::create([
            'work_order_stage_id' => $stage->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'unit' => $product->unit,
            'quantity' => (float) $data['quantity'],
            'rate' => (float) ($data['rate'] ?? $product->rate),
            'gst_rate' => (float) $product->gst_rate,
        ]);

        StockService::record($product, 'out', (float) $row->quantity, $workOrder->number, 'Used in '.$stage->name, null, null, $request->user('admin')->id ?? null);

        return back()->with('success', $product->name.' ('.$row->quantity.' '.$product->unit.') recorded and deducted from stock.');
    }

    public function updateStageProduct(Request $request, WorkOrder $workOrder, WorkOrderStage $stage, WorkOrderStageProduct $stageProduct)
    {
        if ($stageProduct->work_order_stage_id !== $stage->id || $stage->work_order_id !== $workOrder->id || $workOrder->isCancelled()) {
            return back()->with('error', 'Invalid stage material.');
        }

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'rate' => ['required', 'numeric', 'min:0'],
        ]);

        $oldQty = (float) $stageProduct->quantity;
        $newQty = round((float) $data['quantity'], 3);
        $delta = round($newQty - $oldQty, 3);

        if ($stageProduct->product && abs($delta) > 0.0001) {
            $product = Product::find($stageProduct->product_id);
            StockService::record($product, $delta > 0 ? 'out' : 'in', abs($delta), $workOrder->number, 'Quantity adjusted for '.$stage->name, null, null, $request->user('admin')->id ?? null);
        }

        $stageProduct->update($data);

        return back()->with('success', 'Material updated.');
    }

    public function destroyStageProduct(Request $request, WorkOrder $workOrder, WorkOrderStage $stage, WorkOrderStageProduct $stageProduct)
    {
        if ($stageProduct->work_order_stage_id !== $stage->id || $stage->work_order_id !== $workOrder->id || $workOrder->isCancelled()) {
            return back()->with('error', 'Invalid stage material.');
        }

        if ($stageProduct->product) {
            StockService::record($stageProduct->product, 'in', (float) $stageProduct->quantity, $workOrder->number, 'Removed from '.$stage->name, null, null, $request->user('admin')->id ?? null);
        }

        $stageProduct->delete();

        return back()->with('success', 'Material removed and stock restored.');
    }

    public function generateInvoice(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->isCancelled()) {
            return back()->with('error', 'Cancelled work orders cannot be invoiced.');
        }

        if ($workOrder->invoice) {
            return redirect()->route('admin.invoices.show', $workOrder->invoice)
                ->with('error', 'This work order is already invoiced ('.$workOrder->invoice->number.').');
        }

        $rows = WorkOrderStageProduct::whereHas('stage', fn ($q) => $q->where('work_order_id', $workOrder->id))
            ->with('stage')
            ->get()
            ->sortBy(fn ($r) => $r->stage->sort_order)
            ->values();

        if ($rows->isEmpty()) {
            return back()->with('error', 'No materials have been recorded yet. Add materials to stages first.');
        }

        $invoice = DB::transaction(function () use ($workOrder, $rows, $request) {
            $invoice = Invoice::create([
                'number' => InvoiceNumberer::next(),
                'customer_id' => $workOrder->customer_id,
                'customer_name' => $workOrder->customer_name,
                'work_order_id' => $workOrder->id,
                'invoice_date' => now()->toDateString(),
                'status' => 'unpaid',
                'terms' => config('invoice.terms', ''),
                'created_by' => $request->user('admin')->id ?? null,
            ]);

            foreach ($rows as $index => $row) {
                $invoice->items()->create([
                    'product_id' => $row->product_id,
                    'name' => $row->name,
                    'unit' => $row->unit,
                    'qty' => (float) $row->quantity,
                    'rate' => (float) $row->rate,
                    'discount' => 0,
                    'gst_rate' => (float) $row->gst_rate,
                    'total' => $row->lineTotal(),
                    'sort_order' => $index,
                ]);
            }

            $this->recalculateInvoice($invoice);

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice '.$invoice->number.' generated from work order '.$workOrder->number.'.');
    }

    private function refreshWorkOrderStatus(WorkOrder $workOrder): void
    {
        $workOrder->refresh()->load('stages');

        if ($workOrder->isCancelled()) {
            return;
        }

        $allDone = $workOrder->stages->every(fn ($s) => in_array($s->status, ['completed', 'skipped'], true));
        $anyDone = $workOrder->stages->contains(fn ($s) => in_array($s->status, ['completed', 'skipped'], true));

        if ($allDone && $workOrder->stages->isNotEmpty()) {
            $workOrder->update([
                'status' => 'completed',
                'started_at' => $workOrder->started_at ?? now(),
                'completed_at' => now(),
            ]);
        } elseif ($anyDone && ! in_array($workOrder->status, ['completed'], true)) {
            $workOrder->update([
                'status' => 'in_progress',
                'started_at' => $workOrder->started_at ?? now(),
                'completed_at' => null,
            ]);
        }
    }

    private function recalculateInvoice(Invoice $invoice): void
    {
        $invoice->refresh()->load('items');

        $subtotal = round((float) $invoice->items->sum(fn ($i) => (float) $i->qty * (float) $i->rate), 2);
        $discountTotal = round((float) $invoice->items->sum('discount'), 2);
        $gstTotal = round((float) $invoice->items->sum(fn ($i) => $i->gstAmount()), 2);

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'gst_total' => $gstTotal,
            'grand_total' => round(max(0, $subtotal - $discountTotal + $gstTotal), 2),
        ]);
    }
}
