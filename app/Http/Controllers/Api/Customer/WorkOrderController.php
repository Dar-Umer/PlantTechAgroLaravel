<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageProduct;
use App\Notifications\CustomerBookedService;
use App\Services\StockService;
use App\Support\AppConfig;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::where('customer_id', $request->user()->id)
            ->with(['agent:id,name', 'orchard:id,orchard_id,name,is_company_established', 'invoice:id,number,status,grand_total,amount_paid', 'stages:work_order_id,status']);

        if ($status = $request->query('status')) {
            abort_unless(in_array($status, array_keys(WorkOrder::STATUSES), true), 422, 'Invalid status filter.');
            $query->where('status', $status);
        }

        $paginated = $query->latest()->paginate(15)->withQueryString();

        return response()->json([
            'work_orders' => $paginated->map(
                fn (WorkOrder $wo) => static::summary($wo)
            )->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        $workOrder = WorkOrder::where('customer_id', $request->user()->id)
            ->with(['agent:id,name', 'orchard:id,orchard_id,name,is_company_established,area_kanals,tree_count', 'invoice', 'stages.products', 'stages.attachments', 'service'])
            ->findOrFail($id);

        $data = static::summary($workOrder);
        $data['notes'] = $workOrder->notes;
        $data['stages'] = $workOrder->stages->map(function (WorkOrderStage $stage) {
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'description' => $stage->description,
                'sort_order' => $stage->sort_order,
                'status' => $stage->status,
                'status_label' => WorkOrderStage::STATUSES[$stage->status] ?? $stage->status,
                'status_color' => match ($stage->status) {
                    'completed' => 'green',
                    'in_progress' => 'yellow',
                    'skipped' => 'gray',
                    default => 'gray',
                },
                'completed_at' => $stage->completed_at?->toISOString(),
                'requires_photo' => $stage->requires_photo,
                'min_photos' => $stage->min_photos,
                'requires_pdf' => $stage->requires_pdf,
                'notes' => $stage->notes,
                'products' => $stage->products->map(fn ($row) => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'unit' => $row->unit,
                    'quantity' => (float) $row->quantity,
                    'rate' => (float) $row->rate,
                    'gst_rate' => (float) $row->gst_rate,
                    'total' => $row->lineTotal(),
                ])->values(),
                'attachments' => $stage->attachments?->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'original_name' => $a->original_name,
                    'url' => Media::url($a->file_path),
                ])->values() ?? [],
            ];
        })->values();

        return response()->json([
            'work_order' => $data,
            'app_config' => AppConfig::toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $customer = $request->user();

        $data = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'orchard_id' => ['nullable', 'integer', Rule::exists('orchards', 'id')->where('customer_id', $customer->id)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = Service::with('stages.products.product')->findOrFail($data['service_id']);

        if (! $service->is_active) {
            throw ValidationException::withMessages([
                'service_id' => 'This service is currently not available for booking.',
            ]);
        }

        $activeQuery = WorkOrder::where('customer_id', $customer->id)
            ->where('service_id', $service->id)
            ->whereIn('status', ['pending', 'assigned', 'in_progress']);

        if (! empty($data['orchard_id'])) {
            $activeQuery->where('orchard_id', $data['orchard_id']);
        }

        if ($activeQuery->exists()) {
            throw ValidationException::withMessages([
                'service_id' => 'You already have an active request for this service on this orchard.',
            ]);
        }

        $workOrder = DB::transaction(function () use ($data, $customer, $service) {
            $workOrder = WorkOrder::create([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'orchard_id' => $data['orchard_id'] ?? null,
                'assigned_agent_id' => null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'created_by' => null,
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

                    StockService::record(
                        $product,
                        'out',
                        (float) $templateProduct->quantity,
                        $workOrder->number,
                        'Allocated to '.$template->name,
                    );
                }
            }

            return $workOrder;
        });

        $workOrder->load(['agent:id,name', 'invoice', 'stages', 'orchard']);

        // Create a corresponding Lead record so the admin sees the booking in /admin/leads as well as /admin/work-orders
        $orchard = $workOrder->orchard;
        \App\Models\Lead::create([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'service_id' => $service->id,
            'status' => 'new',
            'source' => 'customer_app',
            'converted_customer_id' => $customer->id,
            'notes' => $data['notes'] ?? ('Booked via Customer App for ' . ($orchard ? $orchard->name : 'Orchard')),
            'custom_fields' => array_filter([
                'orchardist_id' => $customer->orchardist_id,
                'orchard_id' => $workOrder->orchard_id,
                'orchard_name' => $orchard?->name,
                'work_order_id' => $workOrder->id,
                'work_order_number' => $workOrder->number,
                'area' => $customer->area ?? $customer->address,
            ]),
        ]);

        Admin::where('is_active', true)->get()->each->notify(new CustomerBookedService($workOrder));

        return response()->json([
            'message' => 'Your service request has been submitted. Our team will reach out shortly.',
            'work_order' => static::summary($workOrder),
        ], 201);
    }

    public static function summary(WorkOrder $workOrder): array
    {
        $stages = $workOrder->relationLoaded('stages') ? $workOrder->stages : collect();
        $total = $stages->count();
        $done = $stages->filter(fn ($s) => in_array($s->status, ['completed', 'skipped'], true))->count();

        return [
            'id' => $workOrder->id,
            'number' => $workOrder->number,
            'customer_name' => $workOrder->customer_name,
            'service_name' => $workOrder->service_name,
            'status' => $workOrder->status,
            'status_label' => WorkOrder::STATUSES[$workOrder->status] ?? $workOrder->status,
            'status_color' => WorkOrder::STATUS_COLORS[$workOrder->status] ?? 'gray',
            'assigned_to' => $workOrder->agent?->name,
            'stages_total' => $total,
            'stages_completed' => $done,
            'progress_percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
            'orchard' => $workOrder->orchard ? [
                'id' => $workOrder->orchard->id,
                'orchard_id' => $workOrder->orchard->orchard_id,
                'name' => $workOrder->orchard->name,
                'is_company_established' => (bool) $workOrder->orchard->is_company_established,
                'company_tag' => $workOrder->orchard->company_tag,
            ] : null,
            'created_at' => $workOrder->created_at?->toISOString(),
            'started_at' => $workOrder->started_at?->toISOString(),
            'completed_at' => $workOrder->completed_at?->toISOString(),
            'invoice' => $workOrder->invoice ? [
                'id' => $workOrder->invoice->id,
                'number' => $workOrder->invoice->number,
                'status' => $workOrder->invoice->status,
                'status_label' => Invoice::STATUSES[$workOrder->invoice->status] ?? $workOrder->invoice->status,
                'grand_total' => (float) $workOrder->invoice->grand_total,
                'balance_due' => $workOrder->invoice->balanceDue(),
            ] : null,
        ];
    }
}