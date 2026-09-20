<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Carbon;

/**
 * Builds a unified chronological statement of account for a customer:
 * invoices are debits (+), payments are credits (−), with a running balance.
 */
class CustomerLedgerService
{
    /**
     * @return array{
     *   customer: Customer,
     *   from: ?Carbon,
     *   to: ?Carbon,
     *   opening: float,
     *   entries: array<int, array{date: Carbon, type: string, label: string, reference: string, debit: float, credit: float, balance: float}>,
     *   total_invoiced: float,
     *   total_paid: float,
     *   closing: float,
     * }
     */
    public static function build(Customer $customer, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $to = $to?->copy()->endOfDay();

        $invoiceBase = $customer->invoices()->whereNotIn('status', ['cancelled']);

        // Opening balance: everything strictly before the from-date.
        $opening = 0.0;
        if ($from) {
            $openingInvoiced = (float) (clone $invoiceBase)
                ->where('invoice_date', '<', $from->toDateString())
                ->sum('grand_total');
            $openingPaid = (float) $customer->invoices()
                ->whereNotIn('status', ['cancelled'])
                ->join('payments', 'payments.invoice_id', '=', 'invoices.id')
                ->where('payments.paid_at', '<', $from->toDateString())
                ->sum('payments.amount');
            $opening = round($openingInvoiced - $openingPaid, 2);
        }

        $invoices = (clone $invoiceBase)
            ->with('payments')
            ->when($from, fn ($q) => $q->where('invoice_date', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->where('invoice_date', '<=', $to->toDateString()))
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $payments = \App\Models\Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.customer_id', $customer->id)
            ->whereNotIn('invoices.status', ['cancelled'])
            ->when($from, fn ($q) => $q->where('payments.paid_at', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->where('payments.paid_at', '<=', $to->toDateString()))
            ->orderBy('payments.paid_at')
            ->orderBy('payments.id')
            ->select('payments.*')
            ->with('invoice:id,number')
            ->get();

        $raw = [];

        foreach ($invoices as $invoice) {
            $raw[] = [
                'date' => $invoice->invoice_date->copy()->startOfDay(),
                'sort' => $invoice->invoice_date->format('Y-m-d').'-'.$invoice->id.'-0',
                'type' => 'invoice',
                'label' => 'Invoice '.$invoice->number,
                'reference' => $invoice->number,
                'debit' => round((float) $invoice->grand_total, 2),
                'credit' => 0.0,
            ];
        }

        foreach ($payments as $payment) {
            $raw[] = [
                'date' => Carbon::parse($payment->paid_at)->startOfDay(),
                'sort' => Carbon::parse($payment->paid_at)->format('Y-m-d').'-'.$payment->id.'-1',
                'type' => 'payment',
                'label' => 'Payment received'.($payment->invoice ? ' — '.$payment->invoice->number : '').($payment->method ? ' ('.(\App\Models\Payment::METHODS[$payment->method] ?? $payment->method).')' : ''),
                'reference' => $payment->reference ?? '',
                'debit' => 0.0,
                'credit' => round((float) $payment->amount, 2),
            ];
        }

        usort($raw, fn ($a, $b) => strcmp($a['sort'], $b['sort']));

        $balance = $opening;
        $entries = [];
        foreach ($raw as $row) {
            $balance = round($balance + $row['debit'] - $row['credit'], 2);
            unset($row['sort']);
            $row['balance'] = $balance;
            $entries[] = $row;
        }

        $totalInvoiced = round(array_sum(array_column($entries, 'debit')), 2);
        $totalPaid = round(array_sum(array_column($entries, 'credit')), 2);

        return [
            'customer' => $customer,
            'from' => $from,
            'to' => $to,
            'opening' => $opening,
            'entries' => $entries,
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'closing' => round($opening + $totalInvoiced - $totalPaid, 2),
        ];
    }
}
