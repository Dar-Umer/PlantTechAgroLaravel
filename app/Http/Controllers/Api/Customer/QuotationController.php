<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\QuotationApprovalService;
use App\Support\AppConfig;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        $quotations = Quotation::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->orWhere('customer_phone', $customer->phone);
        })
        ->with(['service:id,name,slug', 'workOrder:id,number,status'])
        ->latest('date')
        ->latest('id')
        ->paginate(15);

        return response()->json([
            'quotations' => $quotations->map(fn (Quotation $q) => static::summary($q))->values(),
            'pagination' => [
                'current_page' => $quotations->currentPage(),
                'last_page' => $quotations->lastPage(),
                'total' => $quotations->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        $customer = $request->user();

        $quotation = Quotation::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->orWhere('customer_phone', $customer->phone);
        })
        ->with(['service:id,name,slug,description', 'items', 'workOrder:id,number,status'])
        ->findOrFail($id);

        return response()->json([
            'quotation' => [
                'id' => $quotation->id,
                'number' => $quotation->number,
                'status' => $quotation->status,
                'status_label' => match ($quotation->status) {
                    'sent' => 'Pending Your Approval',
                    'approved' => 'Approved',
                    'rejected' => 'Declined',
                    default => 'Draft',
                },
                'status_color' => match ($quotation->status) {
                    'sent' => 'amber',
                    'approved' => 'green',
                    'rejected' => 'red',
                    default => 'gray',
                },
                'service_name' => $quotation->service?->name ?? 'Custom Agricultural Project',
                'date' => $quotation->date?->format('d M Y'),
                'valid_until' => $quotation->valid_until?->format('d M Y'),
                'is_valid' => $quotation->isValid(),
                'can_approve' => $quotation->canApprove(),
                'subtotal' => (float) $quotation->subtotal,
                'discount_total' => (float) $quotation->discount_total,
                'gst_total' => (float) $quotation->gst_total,
                'grand_total' => (float) $quotation->grand_total,
                'notes' => $quotation->notes,
                'terms' => $quotation->terms,
                'pdf_url' => route('admin.quotations.pdf', $quotation),
                'items' => $quotation->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'qty' => (float) $item->qty,
                    'rate' => (float) $item->rate,
                    'gst_rate' => (float) $item->gst_rate,
                    'discount' => (float) $item->discount,
                    'total' => (float) $item->total,
                ])->values(),
                'work_order' => $quotation->workOrder ? [
                    'id' => $quotation->workOrder->id,
                    'number' => $quotation->workOrder->number,
                    'status' => $quotation->workOrder->status,
                ] : null,
            ],
            'app_config' => AppConfig::toArray(),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $customer = $request->user();

        $quotation = Quotation::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->orWhere('customer_phone', $customer->phone);
        })->findOrFail($id);

        if (! $quotation->canApprove()) {
            return response()->json([
                'message' => 'This quotation has already been approved or is no longer eligible for acceptance.',
            ], 422);
        }

        $workOrder = QuotationApprovalService::approveAndStartWork(
            quotation: $quotation,
            approvingCustomer: $customer
        );

        return response()->json([
            'message' => 'Quotation approved successfully! Your project is now scheduled.',
            'work_order' => [
                'id' => $workOrder->id,
                'number' => $workOrder->number,
                'status' => $workOrder->status,
            ],
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $customer = $request->user();

        $quotation = Quotation::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->orWhere('customer_phone', $customer->phone);
        })->findOrFail($id);

        if ($quotation->status === 'approved' || $quotation->work_order_id) {
            return response()->json([
                'message' => 'An approved project quotation cannot be declined.',
            ], 422);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = $data['reason'] ?? 'Customer requested revisions';
        $quotation->update([
            'status' => 'rejected',
            'notes' => trim(($quotation->notes ? $quotation->notes . "\n" : '') . "[Customer Feedback: " . $reason . "]"),
        ]);

        return response()->json([
            'message' => 'Quotation declined. Our team has been notified and will reach out with an adjusted proposal.',
        ]);
    }

    public static function summary(Quotation $q): array
    {
        return [
            'id' => $q->id,
            'number' => $q->number,
            'service_name' => $q->service?->name ?? 'Custom Agricultural Project',
            'status' => $q->status,
            'status_label' => match ($q->status) {
                'sent' => 'Pending Your Approval',
                'approved' => 'Approved',
                'rejected' => 'Declined',
                default => 'Draft',
            },
            'status_color' => match ($q->status) {
                'sent' => 'amber',
                'approved' => 'green',
                'rejected' => 'red',
                default => 'gray',
            },
            'grand_total' => (float) $q->grand_total,
            'date' => $q->date?->format('d M Y'),
            'valid_until' => $q->valid_until?->format('d M Y'),
            'is_valid' => $q->isValid(),
            'can_approve' => $q->canApprove(),
            'work_order_number' => $q->workOrder?->number,
        ];
    }
}
