<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Models\WorkOrderStageProduct;
use Illuminate\Support\Facades\DB;

class WorkOrderInvoiceService
{
    /**
     * Build an invoice from a work order's recorded stage materials.
     *
     * Returns null when the order is cancelled, already invoiced, or has no
     * recorded materials (so only complete, material-bearing orders invoice).
     */
    public function generate(WorkOrder $workOrder, ?int $createdBy = null): ?Invoice
    {
        if ($workOrder->isCancelled() || $workOrder->invoice) {
            return null;
        }

        $rows = WorkOrderStageProduct::whereHas('stage', fn ($q) => $q->where('work_order_id', $workOrder->id))
            ->with('stage')
            ->get()
            ->sortBy(fn ($r) => $r->stage->sort_order)
            ->values();

        if ($rows->isEmpty()) {
            return null;
        }

        $invoice = DB::transaction(function () use ($workOrder, $rows, $createdBy) {
            $invoice = Invoice::create([
                'number' => InvoiceNumberer::next(),
                'customer_id' => $workOrder->customer_id,
                'customer_name' => $workOrder->customer_name,
                'work_order_id' => $workOrder->id,
                'invoice_date' => now()->toDateString(),
                'status' => 'unpaid',
                'terms' => config('invoice.terms', ''),
                'created_by' => $createdBy,
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

            $this->recalculate($invoice);

            return $invoice;
        });

        return $invoice;
    }

    public function recalculate(Invoice $invoice): void
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