<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function gstReport(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        // B2B — invoices where the customer has a GSTIN
        $b2bInvoices = Invoice::with(['customer:id,name,gstin', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->whereHas('customer', fn ($q) => $q->whereNotNull('gstin')->where('gstin', '!=', ''))
            ->orderByDesc('invoice_date')
            ->get()
            ->map(fn ($inv) => $this->invoiceSummary($inv));

        // B2C — invoices where customer has no GSTIN or is guest
        $b2cInvoices = Invoice::with(['customer:id,name,gstin', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->where(function ($q) {
                $q->whereDoesntHave('customer')
                    ->orWhereHas('customer', fn ($cq) => $cq->whereNull('gstin')->orWhere('gstin', ''));
            })
            ->orderByDesc('invoice_date')
            ->get()
            ->map(fn ($inv) => $this->invoiceSummary($inv));

        // HSN Summary — aggregate by HSN code across all non-cancelled invoices in range
        $hsnRows = InvoiceItem::query()
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString())
            ->select(
                DB::raw("COALESCE(invoice_items.hsn_code, 'N/A') as hsn_code"),
                'invoice_items.name',
                DB::raw('SUM(invoice_items.qty) as total_qty'),
                DB::raw('SUM(invoice_items.qty * invoice_items.rate) as taxable_value'),
                DB::raw('AVG(invoice_items.gst_rate) as avg_gst_rate'),
                DB::raw('SUM(invoice_items.qty * invoice_items.rate * invoice_items.gst_rate / 100) as gst_amount'),
            )
            ->groupBy('invoice_items.hsn_code', 'invoice_items.name')
            ->orderByDesc('taxable_value')
            ->get()
            ->map(function ($row) {
                $taxable = (float) $row->taxable_value;
                $gstAmt  = (float) $row->gst_amount;
                return [
                    'hsn_code'      => $row->hsn_code,
                    'description'   => $row->name,
                    'total_qty'     => (float) $row->total_qty,
                    'taxable_value' => $taxable,
                    'cgst'          => round($gstAmt / 2, 2),
                    'sgst'          => round($gstAmt / 2, 2),
                    'total_tax'     => round($gstAmt, 2),
                    'grand_total'   => round($taxable + $gstAmt, 2),
                ];
            });

        // Totals
        $totals = [
            'taxable'   => round($b2bInvoices->sum('taxable_value') + $b2cInvoices->sum('taxable_value'), 2),
            'cgst'      => round($b2bInvoices->sum('cgst') + $b2cInvoices->sum('cgst'), 2),
            'sgst'      => round($b2bInvoices->sum('sgst') + $b2cInvoices->sum('sgst'), 2),
            'total_tax' => round($b2bInvoices->sum('total_tax') + $b2cInvoices->sum('total_tax'), 2),
            'grand'     => round($b2bInvoices->sum('grand_total') + $b2cInvoices->sum('grand_total'), 2),
        ];

        return view('admin.reports.gst', compact(
            'from', 'to', 'b2bInvoices', 'b2cInvoices', 'hsnRows', 'totals'
        ));
    }

    public function exportGst(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $tab = $request->query('tab', 'b2b'); // b2b | b2c | hsn

        $filename = "gst_{$tab}_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $rows = match ($tab) {
            'b2b' => $this->b2bCsvRows($from, $to),
            'b2c' => $this->b2cCsvRows($from, $to),
            default => $this->hsnCsvRows($from, $to),
        };

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => $this->sanitizeCsv($v), $row));
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function invoiceSummary(Invoice $inv): array
    {
        $taxable   = (float) $inv->items->sum(fn ($i) => (float) $i->qty * (float) $i->rate - (float) $i->discount);
        $gstAmount = (float) $inv->items->sum(fn ($i) => $i->gstAmount());

        return [
            'invoice_number'  => $inv->number,
            'invoice_date'    => $inv->invoice_date,
            'customer_name'   => $inv->customer_name ?? $inv->customer?->name ?? '—',
            'gstin'           => $inv->customer?->gstin ?? '',
            'taxable_value'   => round($taxable, 2),
            'cgst'            => round($gstAmount / 2, 2),
            'sgst'            => round($gstAmount / 2, 2),
            'total_tax'       => round($gstAmount, 2),
            'grand_total'     => round((float) $inv->grand_total, 2),
            'status'          => $inv->status,
        ];
    }

    private function b2bCsvRows(Carbon $from, Carbon $to): \Generator
    {
        yield ['Invoice No.', 'Invoice Date', 'Customer', 'GSTIN', 'Taxable Value', 'CGST', 'SGST', 'Total Tax', 'Grand Total', 'Status'];
        $invoices = Invoice::with(['customer:id,name,gstin', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->whereHas('customer', fn ($q) => $q->whereNotNull('gstin')->where('gstin', '!=', ''))
            ->orderByDesc('invoice_date')
            ->cursor();

        foreach ($invoices as $inv) {
            $s = $this->invoiceSummary($inv);
            yield array_values($s);
        }
    }

    private function b2cCsvRows(Carbon $from, Carbon $to): \Generator
    {
        yield ['Invoice No.', 'Invoice Date', 'Customer', 'Taxable Value', 'CGST', 'SGST', 'Total Tax', 'Grand Total', 'Status'];
        $invoices = Invoice::with(['customer:id,name,gstin', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->where(function ($q) {
                $q->whereDoesntHave('customer')
                    ->orWhereHas('customer', fn ($cq) => $cq->whereNull('gstin')->orWhere('gstin', ''));
            })
            ->orderByDesc('invoice_date')
            ->cursor();

        foreach ($invoices as $inv) {
            $s = $this->invoiceSummary($inv);
            yield [$s['invoice_number'], $s['invoice_date'], $s['customer_name'], $s['taxable_value'], $s['cgst'], $s['sgst'], $s['total_tax'], $s['grand_total'], $s['status']];
        }
    }

    private function hsnCsvRows(Carbon $from, Carbon $to): \Generator
    {
        yield ['HSN/SAC Code', 'Description', 'Total Qty', 'Taxable Value', 'CGST', 'SGST', 'Total Tax', 'Grand Total'];
        $rows = InvoiceItem::query()
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString())
            ->select(
                DB::raw("COALESCE(invoice_items.hsn_code, 'N/A') as hsn_code"),
                'invoice_items.name',
                DB::raw('SUM(invoice_items.qty) as total_qty'),
                DB::raw('SUM(invoice_items.qty * invoice_items.rate) as taxable_value'),
                DB::raw('SUM(invoice_items.qty * invoice_items.rate * invoice_items.gst_rate / 100) as gst_amount'),
            )
            ->groupBy('invoice_items.hsn_code', 'invoice_items.name')
            ->orderByDesc('taxable_value')
            ->cursor();

        foreach ($rows as $row) {
            $taxable = (float) $row->taxable_value;
            $gst     = (float) $row->gst_amount;
            yield [$row->hsn_code, $row->name, (float) $row->total_qty, $taxable, round($gst / 2, 2), round($gst / 2, 2), round($gst, 2), round($taxable + $gst, 2)];
        }
    }

    private function sanitizeCsv(mixed $value): string
    {
        $str = (string) $value;
        // Strip formula-injection characters
        if (in_array(substr($str, 0, 1), ['=', '+', '-', '@', "\t", "\r"], true)) {
            $str = "'" . $str;
        }
        return $str;
    }
}
