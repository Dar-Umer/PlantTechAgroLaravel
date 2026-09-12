<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::where('customer_id', $request->user()->id)
            ->with('workOrder:id,number');

        if ($status = $request->query('status')) {
            abort_unless(in_array($status, array_keys(Invoice::STATUSES), true), 422, 'Invalid status filter.');
            $query->where('status', $status);
        }

        $paginated = $query->latest()->paginate(15)->withQueryString();

        return response()->json([
            'invoices' => $paginated->map(fn (Invoice $invoice) => static::summary($invoice))->values(),
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
        $invoice = Invoice::where('customer_id', $request->user()->id)
            ->with(['items', 'payments', 'workOrder:id,number'])
            ->findOrFail($id);

        $data = static::summary($invoice);
        $data['work_order_number'] = $invoice->workOrder?->number;
        $data['terms'] = $invoice->terms;
        $data['notes'] = $invoice->notes;
        $data['items'] = $invoice->items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'unit' => $item->unit,
            'qty' => (float) $item->qty,
            'rate' => (float) $item->rate,
            'discount' => (float) $item->discount,
            'gst_rate' => (float) $item->gst_rate,
            'total' => $item->lineTotal(),
        ])->values();
        $data['payments'] = $invoice->payments->map(fn (Payment $payment) => [
            'id' => $payment->id,
            'amount' => (float) $payment->amount,
            'method' => $payment->method,
            'method_label' => Payment::METHODS[$payment->method] ?? $payment->method,
            'paid_at' => $payment->paid_at?->toDateString(),
            'reference' => $payment->reference,
            'note' => $payment->note,
        ])->values();

        return response()->json(['invoice' => $data]);
    }

    public static function summary(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'work_order_id' => $invoice->work_order_id,
            'invoice_date' => $invoice->invoice_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'status' => $invoice->status,
            'status_label' => Invoice::STATUSES[$invoice->status] ?? $invoice->status,
            'status_color' => Invoice::STATUS_COLORS[$invoice->status] ?? 'gray',
            'subtotal' => (float) $invoice->subtotal,
            'discount_total' => (float) $invoice->discount_total,
            'gst_total' => (float) $invoice->gst_total,
            'grand_total' => (float) $invoice->grand_total,
            'amount_paid' => (float) $invoice->amount_paid,
            'balance_due' => $invoice->balanceDue(),
            'is_overdue' => $invoice->isOverdue(),
        ];
    }
}