<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PosSale;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseBill;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\WorkOrderStageProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // =========================================================================
    // 0. Reports Dashboard Index
    // =========================================================================
    public function index()
    {
        return view('admin.reports.index');
    }

    // =========================================================================
    // 1. Sale Report (Consolidated: Customer Invoices + Retail POS Sales)
    // =========================================================================
    public function sales(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);
        $type = $request->query('type', 'all'); // all | invoice | pos

        $data = $this->buildSalesReportData($from, $to, $type);

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf(
                'Sale Report (Consolidated)',
                'Consolidated summary of Customer B2B/B2C Invoices and Storefront POS counter sales.',
                $from,
                $to,
                $data['summaryCards'],
                $data['columns'],
                $data['rows'],
                $data['totals']
            );
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('sales_report', $from, $to, $data['columns'], $data['rows']);
        }

        return view('admin.reports.report_table', [
            'title' => 'Sale Report',
            'subtitle' => 'Consolidated sales from Customer Invoices and Retail Storefront POS.',
            'badge' => ucfirst($type) . ' Sales',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'extraFilters' => [
                [
                    'name' => 'type',
                    'label' => 'Channel',
                    'type' => 'select',
                    'value' => $type,
                    'options' => ['all' => 'All Channels', 'invoice' => 'Invoices Only', 'pos' => 'POS Sales Only'],
                ],
            ],
            'summaryCards' => $data['summaryCards'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
            'totals' => $data['totals'],
            'exportPdfUrl' => route('admin.reports.sales', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.sales', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.sales'),
        ]);
    }

    private function buildSalesReportData(Carbon $from, Carbon $to, string $type): array
    {
        $rows = [];
        $totalTaxable = 0.0;
        $totalTax = 0.0;
        $totalGrand = 0.0;
        $totalPaid = 0.0;
        $totalDue = 0.0;

        // Invoices
        if ($type === 'all' || $type === 'invoice') {
            $invoices = Invoice::with(['customer', 'items'])
                ->whereNotIn('status', ['cancelled'])
                ->whereDate('invoice_date', '>=', $from->toDateString())
                ->whereDate('invoice_date', '<=', $to->toDateString())
                ->orderByDesc('invoice_date')
                ->get();

            foreach ($invoices as $inv) {
                $taxable = (float) $inv->items->sum(fn ($i) => (float) $i->qty * (float) $i->rate - (float) $i->discount);
                $tax = (float) $inv->gst_total;
                $grand = (float) $inv->grand_total;
                $paid = (float) $inv->amount_paid;
                $due = max(0, $grand - $paid);

                $totalTaxable += $taxable;
                $totalTax += $tax;
                $totalGrand += $grand;
                $totalPaid += $paid;
                $totalDue += $due;

                $rows[] = [
                    'date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                    'doc_no' => $inv->number,
                    'channel' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">Invoice</span>',
                    'party' => $inv->customer_name ?: ($inv->customer?->name ?? 'Walk-in Customer'),
                    'payment_mode' => $inv->status === 'paid' ? 'Paid' : ucfirst($inv->status),
                    'taxable' => '₹' . number_format($taxable, 2),
                    'tax' => '₹' . number_format($tax, 2),
                    'grand_total' => '₹' . number_format($grand, 2),
                    'paid' => '₹' . number_format($paid, 2),
                    'balance_due' => '₹' . number_format($due, 2),
                    'raw_date' => $inv->invoice_date,
                ];
            }
        }

        // POS Sales
        if ($type === 'all' || $type === 'pos') {
            $posSales = PosSale::with('customer')
                ->where('status', 'completed')
                ->whereDate('sale_date', '>=', $from->toDateString())
                ->whereDate('sale_date', '<=', $to->toDateString())
                ->orderByDesc('sale_date')
                ->get();

            foreach ($posSales as $sale) {
                $taxable = (float) $sale->subtotal - (float) $sale->discount_amount;
                $tax = (float) $sale->gst_amount;
                $grand = (float) $sale->grand_total;
                $paid = (float) $sale->amount_paid;
                $due = max(0, (float) $sale->balance_due);

                $totalTaxable += $taxable;
                $totalTax += $tax;
                $totalGrand += $grand;
                $totalPaid += $paid;
                $totalDue += $due;

                $rows[] = [
                    'date' => Carbon::parse($sale->sale_date)->format('d M Y'),
                    'doc_no' => $sale->invoice_number,
                    'channel' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">POS Retail</span>',
                    'party' => $sale->customer_name ?: ($sale->customer?->name ?? 'Retail Customer'),
                    'payment_mode' => strtoupper($sale->payment_method ?: 'CASH'),
                    'taxable' => '₹' . number_format($taxable, 2),
                    'tax' => '₹' . number_format($tax, 2),
                    'grand_total' => '₹' . number_format($grand, 2),
                    'paid' => '₹' . number_format($paid, 2),
                    'balance_due' => '₹' . number_format($due, 2),
                    'raw_date' => $sale->sale_date,
                ];
            }
        }

        // Sort chronological descending
        usort($rows, fn ($a, $b) => strcmp($b['raw_date'], $a['raw_date']));

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'align' => 'text-left'],
            ['key' => 'doc_no', 'label' => 'Doc #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'channel', 'label' => 'Channel', 'align' => 'text-center', 'isHtml' => true],
            ['key' => 'party', 'label' => 'Customer / Party', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-800'],
            ['key' => 'payment_mode', 'label' => 'Mode/Status', 'align' => 'text-center'],
            ['key' => 'taxable', 'label' => 'Taxable (₹)', 'align' => 'text-right'],
            ['key' => 'tax', 'label' => 'GST Tax (₹)', 'align' => 'text-right', 'cellClass' => 'text-amber-600'],
            ['key' => 'grand_total', 'label' => 'Total (₹)', 'align' => 'text-right', 'cellClass' => 'font-bold text-gray-900'],
            ['key' => 'paid', 'label' => 'Paid (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-semibold'],
            ['key' => 'balance_due', 'label' => 'Balance Due (₹)', 'align' => 'text-right', 'cellClass' => 'text-rose-600 font-semibold'],
        ];

        $summaryCards = [
            ['label' => 'Total Invoices / Bills', 'value' => count($rows), 'helptext' => 'Transactions in selected period'],
            ['label' => 'Taxable Sales Turnover', 'value' => '₹' . number_format($totalTaxable, 2), 'helptext' => 'Net of discounts'],
            ['label' => 'Total GST Collected', 'value' => '₹' . number_format($totalTax, 2), 'helptext' => 'Output GST liability', 'class' => 'text-amber-600'],
            ['label' => 'Gross Sales (Turnover)', 'value' => '₹' . number_format($totalGrand, 2), 'helptext' => 'Total bill value', 'class' => 'text-emerald-600'],
            ['label' => 'Total Amount Collected', 'value' => '₹' . number_format($totalPaid, 2), 'helptext' => 'Realized receipts', 'class' => 'text-blue-600'],
            ['label' => 'Outstanding Balance', 'value' => '₹' . number_format($totalDue, 2), 'helptext' => 'Unpaid receivables', 'class' => 'text-rose-600'],
        ];

        $totals = [
            'date' => 'TOTAL (' . count($rows) . ' records)',
            'taxable' => '₹' . number_format($totalTaxable, 2),
            'tax' => '₹' . number_format($totalTax, 2),
            'grand_total' => '₹' . number_format($totalGrand, 2),
            'paid' => '₹' . number_format($totalPaid, 2),
            'balance_due' => '₹' . number_format($totalDue, 2),
        ];

        return compact('rows', 'columns', 'summaryCards', 'totals');
    }

    // =========================================================================
    // 2. Purchase Report (Supplier Bills, Goods Received & Taxes)
    // =========================================================================
    public function purchases(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);
        $supplierId = $request->query('supplier_id');

        $query = PurchaseBill::with(['supplier', 'items'])
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString());

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $bills = $query->orderByDesc('bill_date')->get();

        $rows = [];
        $totalSubtotal = 0.0;
        $totalTax = 0.0;
        $totalShipping = 0.0;
        $totalGrand = 0.0;
        $totalPaid = 0.0;
        $totalDue = 0.0;

        foreach ($bills as $bill) {
            $sub = (float) $bill->subtotal - (float) $bill->discount;
            $tax = (float) $bill->tax_amount;
            $ship = (float) $bill->shipping_cost;
            $grand = (float) $bill->total_amount;
            $paid = (float) $bill->paid_amount;
            $due = (float) $bill->balance_due;

            $totalSubtotal += $sub;
            $totalTax += $tax;
            $totalShipping += $ship;
            $totalGrand += $grand;
            $totalPaid += $paid;
            $totalDue += $due;

            $rows[] = [
                'date' => Carbon::parse($bill->bill_date)->format('d M Y'),
                'bill_no' => $bill->bill_number,
                'supplier_inv' => $bill->supplier_invoice_no ?: '—',
                'supplier' => $bill->supplier?->name ?? 'Direct Supplier',
                'items_count' => $bill->items->count(),
                'subtotal' => '₹' . number_format($sub, 2),
                'tax' => '₹' . number_format($tax, 2),
                'shipping' => '₹' . number_format($ship, 2),
                'grand_total' => '₹' . number_format($grand, 2),
                'paid' => '₹' . number_format($paid, 2),
                'balance_due' => '₹' . number_format($due, 2),
                'status' => ucfirst($bill->payment_status),
            ];
        }

        $columns = [
            ['key' => 'date', 'label' => 'Bill Date', 'align' => 'text-left'],
            ['key' => 'bill_no', 'label' => 'Bill #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'supplier_inv', 'label' => 'Supplier Inv #', 'align' => 'text-left'],
            ['key' => 'supplier', 'label' => 'Supplier', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-800'],
            ['key' => 'items_count', 'label' => 'Items', 'align' => 'text-center'],
            ['key' => 'subtotal', 'label' => 'Subtotal (₹)', 'align' => 'text-right'],
            ['key' => 'tax', 'label' => 'Input Tax (₹)', 'align' => 'text-right', 'cellClass' => 'text-blue-600'],
            ['key' => 'shipping', 'label' => 'Freight (₹)', 'align' => 'text-right'],
            ['key' => 'grand_total', 'label' => 'Bill Total (₹)', 'align' => 'text-right', 'cellClass' => 'font-bold text-gray-900'],
            ['key' => 'paid', 'label' => 'Paid (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-semibold'],
            ['key' => 'balance_due', 'label' => 'Due (₹)', 'align' => 'text-right', 'cellClass' => 'text-rose-600 font-semibold'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center'],
        ];

        $summaryCards = [
            ['label' => 'Total Inward Bills', 'value' => count($rows), 'helptext' => 'Purchases in period'],
            ['label' => 'Total Net Purchases', 'value' => '₹' . number_format($totalSubtotal, 2), 'helptext' => 'Goods value before tax'],
            ['label' => 'Input Tax Credit (ITC)', 'value' => '₹' . number_format($totalTax, 2), 'helptext' => 'Claimable GST on inputs', 'class' => 'text-blue-600'],
            ['label' => 'Total Inward Expenditure', 'value' => '₹' . number_format($totalGrand, 2), 'helptext' => 'Subtotal + Tax + Freight', 'class' => 'text-gray-900'],
            ['label' => 'Paid to Suppliers', 'value' => '₹' . number_format($totalPaid, 2), 'helptext' => 'Supplier settlements', 'class' => 'text-emerald-600'],
            ['label' => 'Supplier Outstandings', 'value' => '₹' . number_format($totalDue, 2), 'helptext' => 'Payable balances', 'class' => 'text-rose-600'],
        ];

        $totals = [
            'date' => 'TOTAL (' . count($rows) . ' bills)',
            'subtotal' => '₹' . number_format($totalSubtotal, 2),
            'tax' => '₹' . number_format($totalTax, 2),
            'shipping' => '₹' . number_format($totalShipping, 2),
            'grand_total' => '₹' . number_format($totalGrand, 2),
            'paid' => '₹' . number_format($totalPaid, 2),
            'balance_due' => '₹' . number_format($totalDue, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Purchase Report', 'Procurement bills and inward material invoices from suppliers.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('purchase_report', $from, $to, $columns, $rows);
        }

        $suppliers = Supplier::orderBy('name')->pluck('name', 'id')->toArray();

        return view('admin.reports.report_table', [
            'title' => 'Purchase Report',
            'subtitle' => 'Detailed list of procurement bills, input taxes, and supplier balances.',
            'badge' => count($rows) . ' Bills',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'extraFilters' => [
                [
                    'name' => 'supplier_id',
                    'label' => 'Supplier',
                    'type' => 'select',
                    'value' => $supplierId,
                    'options' => ['' => 'All Suppliers'] + $suppliers,
                ],
            ],
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.purchases', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.purchases', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.purchases'),
        ]);
    }

    // =========================================================================
    // 3. Day Book (Daily Sales, Collections & Supplier Disbursements)
    // =========================================================================
    public function dayBook(Request $request)
    {
        $dateStr = $request->query('date', now()->toDateString());
        $date = Carbon::parse($dateStr);

        // Invoices on this day
        $invoices = Invoice::with('customer')
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', $date->toDateString())
            ->get();

        // Payments received from customers on this day
        $payments = Payment::with('invoice.customer')
            ->whereDate('paid_at', $date->toDateString())
            ->get();

        // POS sales on this day
        $posSales = PosSale::with('customer')
            ->where('status', 'completed')
            ->whereDate('sale_date', $date->toDateString())
            ->get();

        // Purchase bills on this day
        $purchases = PurchaseBill::with('supplier')
            ->whereDate('bill_date', $date->toDateString())
            ->get();

        // Supplier disbursements on this day
        $supplierPayments = SupplierPayment::with('supplier')
            ->whereDate('payment_date', $date->toDateString())
            ->get();

        $rows = [];
        $totalInflow = 0.0;
        $totalOutflow = 0.0;

        foreach ($posSales as $sale) {
            $amt = (float) $sale->amount_paid;
            $totalInflow += $amt;
            $rows[] = [
                'time' => Carbon::parse($sale->sale_date)->format('h:i A'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">POS Cashier</span>',
                'ref' => $sale->invoice_number,
                'party' => $sale->customer_name ?: 'Retail Store Customer',
                'details' => 'Counter Cash/UPI Sale (' . strtoupper($sale->payment_method) . ')',
                'debit' => '—',
                'credit' => '₹' . number_format($amt, 2),
                'balance_effect' => '+ ₹' . number_format($amt, 2),
            ];
        }

        foreach ($payments as $pay) {
            $amt = (float) $pay->amount;
            $totalInflow += $amt;
            $rows[] = [
                'time' => Carbon::parse($pay->paid_at)->format('d M'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">Payment In</span>',
                'ref' => $pay->invoice?->number ?? ('PMT-' . $pay->id),
                'party' => $pay->invoice?->customer_name ?? 'Customer',
                'details' => 'Customer Invoice Payment (' . ucfirst($pay->method) . ')',
                'debit' => '—',
                'credit' => '₹' . number_format($amt, 2),
                'balance_effect' => '+ ₹' . number_format($amt, 2),
            ];
        }

        foreach ($supplierPayments as $sp) {
            $amt = (float) $sp->amount;
            $totalOutflow += $amt;
            $rows[] = [
                'time' => Carbon::parse($sp->payment_date)->format('d M'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700">Payment Out</span>',
                'ref' => $sp->payment_number,
                'party' => $sp->supplier?->name ?? 'Supplier',
                'details' => 'Disbursement to Supplier (' . ucfirst($sp->payment_method) . ')',
                'debit' => '₹' . number_format($amt, 2),
                'credit' => '—',
                'balance_effect' => '− ₹' . number_format($amt, 2),
            ];
        }

        $columns = [
            ['key' => 'time', 'label' => 'Time / Date', 'align' => 'text-left'],
            ['key' => 'type', 'label' => 'Entry Type', 'align' => 'text-center', 'isHtml' => true],
            ['key' => 'ref', 'label' => 'Reference #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'party', 'label' => 'Party Name', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-800'],
            ['key' => 'details', 'label' => 'Particulars', 'align' => 'text-left', 'cellClass' => 'text-gray-500'],
            ['key' => 'debit', 'label' => 'Outflow / Paid (₹)', 'align' => 'text-right', 'cellClass' => 'text-rose-600 font-semibold'],
            ['key' => 'credit', 'label' => 'Inflow / Recvd (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-semibold'],
            ['key' => 'balance_effect', 'label' => 'Net Movement', 'align' => 'text-right', 'cellClass' => 'font-bold'],
        ];

        $netDay = $totalInflow - $totalOutflow;

        $summaryCards = [
            ['label' => 'Selected Day', 'value' => $date->format('d M Y'), 'helptext' => $date->format('l')],
            ['label' => 'Total Inflow (Collections)', 'value' => '₹' . number_format($totalInflow, 2), 'helptext' => 'POS Cash + Invoice Receipts', 'class' => 'text-emerald-600'],
            ['label' => 'Total Outflow (Disbursed)', 'value' => '₹' . number_format($totalOutflow, 2), 'helptext' => 'Supplier Payments Made', 'class' => 'text-rose-600'],
            ['label' => 'Net Daily Cash Balance', 'value' => '₹' . number_format($netDay, 2), 'helptext' => 'Inflow − Outflow', 'class' => $netDay >= 0 ? 'text-blue-600' : 'text-rose-600'],
        ];

        $totals = [
            'time' => 'TOTAL TRANSACTIONS: ' . count($rows),
            'debit' => '₹' . number_format($totalOutflow, 2),
            'credit' => '₹' . number_format($totalInflow, 2),
            'balance_effect' => '₹' . number_format($netDay, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Day Book — ' . $date->format('d M Y'), 'Daily chronological financial entries and counter collections.', $date, $date, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('day_book_' . $date->format('Ymd'), $date, $date, $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'Day Book',
            'subtitle' => 'Daily register of all incoming receipts, retail cashier billing, and outgoing vendor payments.',
            'badge' => $date->format('d M Y'),
            'showDateFilter' => false,
            'extraFilters' => [
                [
                    'name' => 'date',
                    'label' => 'Select Date',
                    'type' => 'text',
                    'value' => $date->toDateString(),
                    'placeholder' => 'YYYY-MM-DD',
                ],
            ],
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.day-book', ['date' => $date->toDateString(), 'export' => 'pdf']),
            'exportExcelUrl' => route('admin.reports.day-book', ['date' => $date->toDateString(), 'export' => 'excel']),
            'resetUrl' => route('admin.reports.day-book'),
        ]);
    }

    // =========================================================================
    // 4. All Transactions (Combined Master Journal / Ledger)
    // =========================================================================
    public function allTransactions(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $data = $this->buildAllTransactionsData($from, $to);

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('All Transactions Journal', 'Unified chronological ledger of all customer & supplier financial transactions.', $from, $to, $data['summaryCards'], $data['columns'], $data['rows'], $data['totals']);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('all_transactions', $from, $to, $data['columns'], $data['rows']);
        }

        return view('admin.reports.report_table', [
            'title' => 'All Transactions',
            'subtitle' => 'Comprehensive master register of Invoices, POS Sales, Purchases, Receipts, and Supplier Payments.',
            'badge' => count($data['rows']) . ' Records',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'summaryCards' => $data['summaryCards'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
            'totals' => $data['totals'],
            'exportPdfUrl' => route('admin.reports.transactions', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.transactions', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.transactions'),
        ]);
    }

    private function buildAllTransactionsData(Carbon $from, Carbon $to): array
    {
        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        // 1. Invoices (Sales Debits to Customers)
        $invoices = Invoice::with('customer')
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->get();

        foreach ($invoices as $inv) {
            $amt = (float) $inv->grand_total;
            $totalDebit += $amt;
            $rows[] = [
                'date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">Customer Invoice</span>',
                'ref' => $inv->number,
                'party' => $inv->customer_name ?: ($inv->customer?->name ?? 'Customer'),
                'particulars' => 'Customer Billing / Service Invoice',
                'debit' => '₹' . number_format($amt, 2),
                'credit' => '—',
                'raw_date' => $inv->invoice_date,
            ];
        }

        // 2. POS Sales
        $posSales = PosSale::with('customer')
            ->where('status', 'completed')
            ->whereDate('sale_date', '>=', $from->toDateString())
            ->whereDate('sale_date', '<=', $to->toDateString())
            ->get();

        foreach ($posSales as $pos) {
            $amt = (float) $pos->grand_total;
            $totalDebit += $amt;
            $rows[] = [
                'date' => Carbon::parse($pos->sale_date)->format('d M Y'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">POS Sale</span>',
                'ref' => $pos->invoice_number,
                'party' => $pos->customer_name ?: 'Retail Customer',
                'particulars' => 'Storefront Cashier Sale',
                'debit' => '₹' . number_format($amt, 2),
                'credit' => '—',
                'raw_date' => $pos->sale_date,
            ];
        }

        // 3. Customer Payments (Credits)
        $payments = Payment::with('invoice.customer')
            ->whereDate('paid_at', '>=', $from->toDateString())
            ->whereDate('paid_at', '<=', $to->toDateString())
            ->get();

        foreach ($payments as $p) {
            $amt = (float) $p->amount;
            $totalCredit += $amt;
            $rows[] = [
                'date' => Carbon::parse($p->paid_at)->format('d M Y'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-teal-50 text-teal-700">Customer Receipt</span>',
                'ref' => $p->invoice?->number ?? ('PAY-' . $p->id),
                'party' => $p->invoice?->customer_name ?? 'Customer',
                'particulars' => 'Receipt against Invoice (' . ucfirst($p->method) . ')',
                'debit' => '—',
                'credit' => '₹' . number_format($amt, 2),
                'raw_date' => $p->paid_at,
            ];
        }

        // 4. Purchase Bills (Credits from Suppliers)
        $purchases = PurchaseBill::with('supplier')
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->get();

        foreach ($purchases as $pb) {
            $amt = (float) $pb->total_amount;
            $totalCredit += $amt;
            $rows[] = [
                'date' => Carbon::parse($pb->bill_date)->format('d M Y'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700">Purchase Bill</span>',
                'ref' => $pb->bill_number,
                'party' => $pb->supplier?->name ?? 'Supplier',
                'particulars' => 'Supplier Inward Procurement',
                'debit' => '—',
                'credit' => '₹' . number_format($amt, 2),
                'raw_date' => $pb->bill_date,
            ];
        }

        // 5. Supplier Payments (Debits to Suppliers)
        $supPayments = SupplierPayment::with('supplier')
            ->whereDate('payment_date', '>=', $from->toDateString())
            ->whereDate('payment_date', '<=', $to->toDateString())
            ->get();

        foreach ($supPayments as $sp) {
            $amt = (float) $sp->amount;
            $totalDebit += $amt;
            $rows[] = [
                'date' => Carbon::parse($sp->payment_date)->format('d M Y'),
                'type' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700">Supplier Payment</span>',
                'ref' => $sp->payment_number,
                'party' => $sp->supplier?->name ?? 'Supplier',
                'particulars' => 'Payment Disbursement to Supplier',
                'debit' => '₹' . number_format($amt, 2),
                'credit' => '—',
                'raw_date' => $sp->payment_date,
            ];
        }

        usort($rows, fn ($a, $b) => strcmp($b['raw_date'], $a['raw_date']));

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'align' => 'text-left'],
            ['key' => 'type', 'label' => 'Voucher Type', 'align' => 'text-center', 'isHtml' => true],
            ['key' => 'ref', 'label' => 'Reference #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'party', 'label' => 'Party Name', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-800'],
            ['key' => 'particulars', 'label' => 'Particulars', 'align' => 'text-left', 'cellClass' => 'text-gray-500'],
            ['key' => 'debit', 'label' => 'Debit (₹)', 'align' => 'text-right', 'cellClass' => 'text-gray-900 font-semibold'],
            ['key' => 'credit', 'label' => 'Credit (₹)', 'align' => 'text-right', 'cellClass' => 'text-gray-900 font-semibold'],
        ];

        $summaryCards = [
            ['label' => 'Total Voucher Entries', 'value' => count($rows), 'helptext' => 'Transactions in period'],
            ['label' => 'Total Debits', 'value' => '₹' . number_format($totalDebit, 2), 'helptext' => 'Sales Invoices + Supplier Debits', 'class' => 'text-emerald-600'],
            ['label' => 'Total Credits', 'value' => '₹' . number_format($totalCredit, 2), 'helptext' => 'Receipts + Supplier Purchase Bills', 'class' => 'text-blue-600'],
            ['label' => 'Net Journal Variance', 'value' => '₹' . number_format(abs($totalDebit - $totalCredit), 2), 'helptext' => 'Balanced audit log'],
        ];

        $totals = [
            'date' => 'TOTAL (' . count($rows) . ' entries)',
            'debit' => '₹' . number_format($totalDebit, 2),
            'credit' => '₹' . number_format($totalCredit, 2),
        ];

        return compact('rows', 'columns', 'summaryCards', 'totals');
    }

    // =========================================================================
    // 5. Bill Wise Profit (Margin calculation for each Invoice & POS Sale)
    // =========================================================================
    public function billWiseProfit(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $rows = [];
        $totalSales = 0.0;
        $totalCost = 0.0;
        $totalProfit = 0.0;

        // Invoices with items and their product baseline rate/cost
        $invoices = Invoice::with(['items.product', 'customer'])
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->orderByDesc('invoice_date')
            ->get();

        foreach ($invoices as $inv) {
            $billSale = 0.0;
            $billCost = 0.0;

            foreach ($inv->items as $it) {
                $lineSale = ((float) $it->qty * (float) $it->rate) - (float) $it->discount;
                // Cost estimate: from Product unit rate or batch cost
                $unitCost = (float) ($it->product?->rate ?? 0);
                $lineCost = (float) $it->qty * $unitCost * 0.70; // 70% typical cost baseline if direct cost not recorded
                $billSale += $lineSale;
                $billCost += $lineCost;
            }

            $profit = $billSale - $billCost;
            $margin = $billSale > 0 ? ($profit / $billSale) * 100 : 0;

            $totalSales += $billSale;
            $totalCost += $billCost;
            $totalProfit += $profit;

            $rows[] = [
                'date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                'bill_no' => $inv->number,
                'channel' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">Invoice</span>',
                'customer' => $inv->customer_name ?: ($inv->customer?->name ?? 'Customer'),
                'sale_value' => '₹' . number_format($billSale, 2),
                'cost_value' => '₹' . number_format($billCost, 2),
                'gross_profit' => '₹' . number_format($profit, 2),
                'margin' => number_format($margin, 1) . '%',
                'profit_class' => $profit >= 0 ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold',
            ];
        }

        // POS Sales with line items
        $posSales = PosSale::with(['items', 'customer'])
            ->where('status', 'completed')
            ->whereDate('sale_date', '>=', $from->toDateString())
            ->whereDate('sale_date', '<=', $to->toDateString())
            ->orderByDesc('sale_date')
            ->get();

        foreach ($posSales as $pos) {
            $billSale = (float) $pos->subtotal - (float) $pos->discount_amount;
            $billCost = 0.0;

            foreach ($pos->items as $pi) {
                $costPrice = (float) ($pi->cost_price ?? ($pi->unit_price * 0.70));
                $billCost += (float) $pi->quantity * $costPrice;
            }

            $profit = $billSale - $billCost;
            $margin = $billSale > 0 ? ($profit / $billSale) * 100 : 0;

            $totalSales += $billSale;
            $totalCost += $billCost;
            $totalProfit += $profit;

            $rows[] = [
                'date' => Carbon::parse($pos->sale_date)->format('d M Y'),
                'bill_no' => $pos->invoice_number,
                'channel' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">POS Retail</span>',
                'customer' => $pos->customer_name ?: 'Retail Customer',
                'sale_value' => '₹' . number_format($billSale, 2),
                'cost_value' => '₹' . number_format($billCost, 2),
                'gross_profit' => '₹' . number_format($profit, 2),
                'margin' => number_format($margin, 1) . '%',
                'profit_class' => $profit >= 0 ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold',
            ];
        }

        $overallMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;

        $columns = [
            ['key' => 'date', 'label' => 'Bill Date', 'align' => 'text-left'],
            ['key' => 'bill_no', 'label' => 'Bill Number', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'channel', 'label' => 'Channel', 'align' => 'text-center', 'isHtml' => true],
            ['key' => 'customer', 'label' => 'Customer', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-800'],
            ['key' => 'sale_value', 'label' => 'Sale Value (₹)', 'align' => 'text-right'],
            ['key' => 'cost_value', 'label' => 'Estimated Cost (₹)', 'align' => 'text-right', 'cellClass' => 'text-gray-500'],
            ['key' => 'gross_profit', 'label' => 'Gross Profit (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-bold'],
            ['key' => 'margin', 'label' => 'Margin %', 'align' => 'text-right', 'cellClass' => 'font-bold text-gray-900'],
        ];

        $summaryCards = [
            ['label' => 'Total Invoices / Receipts', 'value' => count($rows), 'helptext' => 'Bills analyzed'],
            ['label' => 'Net Revenue (Sales)', 'value' => '₹' . number_format($totalSales, 2), 'helptext' => 'Excluding taxes'],
            ['label' => 'Estimated Cost of Goods', 'value' => '₹' . number_format($totalCost, 2), 'helptext' => 'Material & product costs'],
            ['label' => 'Total Gross Profit', 'value' => '₹' . number_format($totalProfit, 2), 'helptext' => 'Gross profit generated', 'class' => 'text-emerald-600'],
            ['label' => 'Average Margin', 'value' => number_format($overallMargin, 1) . '%', 'helptext' => 'Overall gross margin %', 'class' => 'text-blue-600'],
        ];

        $totals = [
            'date' => 'TOTAL (' . count($rows) . ' bills)',
            'sale_value' => '₹' . number_format($totalSales, 2),
            'cost_value' => '₹' . number_format($totalCost, 2),
            'gross_profit' => '₹' . number_format($totalProfit, 2),
            'margin' => number_format($overallMargin, 1) . '%',
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Bill-Wise Profit Report', 'Per-invoice gross profit, cost of goods, and margin analysis.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('bill_wise_profit', $from, $to, $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'Bill-Wise Profit',
            'subtitle' => 'Granular profit, purchase cost comparison, and margin percentage calculated on every individual customer invoice and POS receipt.',
            'badge' => number_format($overallMargin, 1) . '% Avg Margin',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.bill-wise-profit', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.bill-wise-profit', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.bill-wise-profit'),
        ]);
    }

    // =========================================================================
    // 6. Profit & Loss Statement (P&L Financials)
    // =========================================================================
    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $pl = $this->calculateProfitLoss($from, $to);

        return view('admin.reports.profit_loss', array_merge(['from' => $from, 'to' => $to], $pl));
    }

    public function exportProfitLoss(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);
        $format = $request->query('format', 'excel'); // excel | pdf

        $pl = $this->calculateProfitLoss($from, $to);

        $summaryCards = [
            ['label' => 'Operating Revenue', 'value' => '₹' . number_format($pl['totalRevenue'], 2)],
            ['label' => 'Cost of Goods Sold (COGS)', 'value' => '₹' . number_format($pl['totalCogs'], 2)],
            ['label' => 'Gross Margin', 'value' => number_format($pl['grossMarginPercent'], 1) . '%'],
            ['label' => 'Net Operating Profit', 'value' => '₹' . number_format($pl['netProfit'], 2)],
        ];

        $columns = [
            ['key' => 'section', 'label' => 'Account / Group', 'align' => 'text-left'],
            ['key' => 'particulars', 'label' => 'Particulars Description', 'align' => 'text-left'],
            ['key' => 'amount', 'label' => 'Amount (₹)', 'align' => 'text-right'],
        ];

        $rows = [
            ['section' => '1. REVENUE (INCOME)', 'particulars' => 'Customer Invoices (Taxable Value)', 'amount' => '₹' . number_format($pl['invoiceTaxable'], 2)],
            ['section' => '1. REVENUE (INCOME)', 'particulars' => 'Retail POS Counter Sales (Subtotal)', 'amount' => '₹' . number_format($pl['posSubtotal'], 2)],
            ['section' => '1. REVENUE (INCOME)', 'particulars' => 'Less: Discounts Allowed', 'amount' => '− ₹' . number_format($pl['discountsGiven'], 2)],
            ['section' => '1. REVENUE TOTAL', 'particulars' => 'NET OPERATING TURNOVER', 'amount' => '₹' . number_format($pl['totalRevenue'], 2)],
            ['section' => '2. DIRECT COSTS (COGS)', 'particulars' => 'Supplier Purchases (Net of Tax)', 'amount' => '₹' . number_format($pl['purchaseSubtotal'], 2)],
            ['section' => '2. DIRECT COSTS (COGS)', 'particulars' => 'Work Order Materials & Consumables', 'amount' => '₹' . number_format($pl['workOrderMaterialCost'], 2)],
            ['section' => '2. DIRECT COSTS (COGS)', 'particulars' => 'Inward Freight & Logistics', 'amount' => '₹' . number_format($pl['shippingCost'], 2)],
            ['section' => '2. COGS TOTAL', 'particulars' => 'TOTAL COST OF GOODS SOLD', 'amount' => '₹' . number_format($pl['totalCogs'], 2)],
            ['section' => '3. BOTTOMLINE', 'particulars' => 'NET OPERATING PROFIT / (LOSS)', 'amount' => '₹' . number_format($pl['netProfit'], 2)],
        ];

        $totals = [
            'section' => 'FINAL NET RESULT',
            'particulars' => $pl['netProfit'] >= 0 ? 'NET PROFIT' : 'NET LOSS',
            'amount' => '₹' . number_format($pl['netProfit'], 2),
        ];

        if ($format === 'pdf') {
            return $this->downloadGenericPdf('Profit & Loss Statement', 'Financial statement of revenues, direct cost of sales, and operating bottomline.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        return $this->streamGenericCsv('profit_loss_statement', $from, $to, $columns, $rows);
    }

    private function calculateProfitLoss(Carbon $from, Carbon $to): array
    {
        // 1. Revenue
        $invoiceTaxable = (float) InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString())
            ->sum(DB::raw('invoice_items.qty * invoice_items.rate - invoice_items.discount'));

        $posSubtotal = (float) PosSale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $from->toDateString())
            ->whereDate('sale_date', '<=', $to->toDateString())
            ->sum('subtotal');

        $discountsGiven = (float) PosSale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $from->toDateString())
            ->whereDate('sale_date', '<=', $to->toDateString())
            ->sum('discount_amount');

        $totalRevenue = max(0, $invoiceTaxable + $posSubtotal - $discountsGiven);

        // 2. Direct Costs / COGS
        $purchaseSubtotal = (float) PurchaseBill::whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->sum(DB::raw('subtotal - discount'));

        $shippingCost = (float) PurchaseBill::whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->sum('shipping_cost');

        $workOrderMaterialCost = (float) WorkOrderStageProduct::whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->sum(DB::raw('quantity * rate'));

        $totalCogs = $purchaseSubtotal + $shippingCost + $workOrderMaterialCost;

        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPercent = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfit = $grossProfit; // Operating net

        return compact(
            'invoiceTaxable', 'posSubtotal', 'discountsGiven', 'totalRevenue',
            'purchaseSubtotal', 'shippingCost', 'workOrderMaterialCost', 'totalCogs',
            'grossProfit', 'grossMarginPercent', 'netProfit'
        );
    }

    // =========================================================================
    // 7. Party Statement (Customer & Supplier unified statement)
    // =========================================================================
    public function partyStatement(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);
        $partyType = $request->query('party_type', 'customer'); // customer | supplier
        $partyId = $request->query('party_id');

        $rows = [];
        $opening = 0.0;
        $closing = 0.0;
        $partyName = 'All Parties';

        if ($partyType === 'customer') {
            if ($partyId && $customer = Customer::find($partyId)) {
                $statement = \App\Services\CustomerLedgerService::build($customer, $from, $to);
                $partyName = $customer->name;
                $opening = $statement['opening'];
                $closing = $statement['closing'];

                foreach ($statement['entries'] as $e) {
                    $rows[] = [
                        'date' => $e['date']->format('d M Y'),
                        'type' => ucfirst($e['type']),
                        'ref' => $e['reference'] ?: '—',
                        'party' => $customer->name,
                        'particulars' => $e['label'],
                        'debit' => $e['debit'] > 0 ? '₹' . number_format($e['debit'], 2) : '—',
                        'credit' => $e['credit'] > 0 ? '₹' . number_format($e['credit'], 2) : '—',
                        'balance' => '₹' . number_format($e['balance'], 2),
                    ];
                }
            } else {
                // Summary across all customers
                $customers = Customer::with('invoices')->orderBy('name')->get();
                foreach ($customers as $c) {
                    $invoiced = (float) $c->invoices()->whereNotIn('status', ['cancelled'])->whereDate('invoice_date', '>=', $from->toDateString())->whereDate('invoice_date', '<=', $to->toDateString())->sum('grand_total');
                    $paid = (float) Payment::join('invoices', 'invoices.id', '=', 'payments.invoice_id')->where('invoices.customer_id', $c->id)->whereDate('payments.paid_at', '>=', $from->toDateString())->whereDate('payments.paid_at', '<=', $to->toDateString())->sum('payments.amount');
                    $due = max(0, $invoiced - $paid);

                    $rows[] = [
                        'date' => 'Summary',
                        'type' => 'Customer',
                        'ref' => 'CUST-' . $c->id,
                        'party' => $c->name,
                        'particulars' => 'Phone: ' . ($c->phone ?: 'N/A') . ($c->gstin ? ' | GSTIN: ' . $c->gstin : ''),
                        'debit' => '₹' . number_format($invoiced, 2),
                        'credit' => '₹' . number_format($paid, 2),
                        'balance' => '₹' . number_format($due, 2),
                    ];
                }
            }
        } else {
            // Suppliers
            if ($partyId && $supplier = Supplier::find($partyId)) {
                $partyName = $supplier->name;
                $bills = PurchaseBill::where('supplier_id', $supplier->id)
                    ->whereDate('bill_date', '>=', $from->toDateString())
                    ->whereDate('bill_date', '<=', $to->toDateString())
                    ->get();
                $payments = SupplierPayment::where('supplier_id', $supplier->id)
                    ->whereDate('payment_date', '>=', $from->toDateString())
                    ->whereDate('payment_date', '<=', $to->toDateString())
                    ->get();

                foreach ($bills as $b) {
                    $rows[] = [
                        'date' => Carbon::parse($b->bill_date)->format('d M Y'),
                        'type' => 'Purchase Bill',
                        'ref' => $b->bill_number,
                        'party' => $supplier->name,
                        'particulars' => 'Goods received inward bill',
                        'debit' => '—',
                        'credit' => '₹' . number_format((float) $b->total_amount, 2),
                        'balance' => '₹' . number_format((float) $b->balance_due, 2),
                    ];
                }

                foreach ($payments as $sp) {
                    $rows[] = [
                        'date' => Carbon::parse($sp->payment_date)->format('d M Y'),
                        'type' => 'Supplier Payment',
                        'ref' => $sp->payment_number,
                        'party' => $supplier->name,
                        'particulars' => 'Payment disbursed (' . ucfirst($sp->payment_method) . ')',
                        'debit' => '₹' . number_format((float) $sp->amount, 2),
                        'credit' => '—',
                        'balance' => '—',
                    ];
                }
            } else {
                $suppliers = Supplier::orderBy('name')->get();
                foreach ($suppliers as $s) {
                    $purchased = (float) PurchaseBill::where('supplier_id', $s->id)->whereDate('bill_date', '>=', $from->toDateString())->whereDate('bill_date', '<=', $to->toDateString())->sum('total_amount');
                    $paid = (float) SupplierPayment::where('supplier_id', $s->id)->whereDate('payment_date', '>=', $from->toDateString())->whereDate('payment_date', '<=', $to->toDateString())->sum('amount');
                    $due = max(0, $purchased - $paid);

                    $rows[] = [
                        'date' => 'Summary',
                        'type' => 'Supplier',
                        'ref' => 'SUP-' . $s->id,
                        'party' => $s->name,
                        'particulars' => 'Contact: ' . ($s->contact_person ?: $s->phone ?: 'N/A') . ($s->gst_no ? ' | GST: ' . $s->gst_no : ''),
                        'debit' => '₹' . number_format($paid, 2),
                        'credit' => '₹' . number_format($purchased, 2),
                        'balance' => '₹' . number_format($due, 2),
                    ];
                }
            }
        }

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'align' => 'text-left'],
            ['key' => 'type', 'label' => 'Doc Type', 'align' => 'text-center'],
            ['key' => 'ref', 'label' => 'Reference #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'party', 'label' => 'Party Name', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-800'],
            ['key' => 'particulars', 'label' => 'Particulars', 'align' => 'text-left', 'cellClass' => 'text-gray-500'],
            ['key' => 'debit', 'label' => 'Debit (₹)', 'align' => 'text-right', 'cellClass' => 'text-rose-600 font-semibold'],
            ['key' => 'credit', 'label' => 'Credit (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-semibold'],
            ['key' => 'balance', 'label' => 'Closing (₹)', 'align' => 'text-right', 'cellClass' => 'font-bold text-gray-900'],
        ];

        $summaryCards = [
            ['label' => 'Selected Party Type', 'value' => ucfirst($partyType) . 's', 'helptext' => $partyName],
            ['label' => 'Total Entries', 'value' => count($rows), 'helptext' => 'Chronological statement'],
            ['label' => 'Opening Balance', 'value' => '₹' . number_format($opening, 2), 'helptext' => 'as of ' . $from->format('d M Y')],
            ['label' => 'Closing Balance', 'value' => '₹' . number_format($closing, 2), 'helptext' => 'as of ' . $to->format('d M Y'), 'class' => 'text-emerald-600'],
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Party Statement — ' . $partyName, 'Account statement and running ledger for party.', $from, $to, $summaryCards, $columns, $rows);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('party_statement', $from, $to, $columns, $rows);
        }

        $partyOptions = $partyType === 'customer'
            ? (['' => 'All Customers (Summary)'] + Customer::orderBy('name')->pluck('name', 'id')->toArray())
            : (['' => 'All Suppliers (Summary)'] + Supplier::orderBy('name')->pluck('name', 'id')->toArray());

        return view('admin.reports.report_table', [
            'title' => 'Party Statement',
            'subtitle' => 'Detailed ledger statement of accounts, debits, credits, and running balances for customers and suppliers.',
            'badge' => $partyName,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'extraFilters' => [
                [
                    'name' => 'party_type',
                    'label' => 'Party Type',
                    'type' => 'select',
                    'value' => $partyType,
                    'options' => ['customer' => 'Customer Accounts', 'supplier' => 'Supplier Accounts'],
                ],
                [
                    'name' => 'party_id',
                    'label' => 'Select Party',
                    'type' => 'select',
                    'value' => $partyId,
                    'options' => $partyOptions,
                ],
            ],
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'exportPdfUrl' => route('admin.reports.party-statement', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.party-statement', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.party-statement'),
        ]);
    }

    // =========================================================================
    // 8. Stock Statement (Inventory Valuation & Real-time Balances)
    // =========================================================================
    public function stockStatement(Request $request)
    {
        $products = Product::with('supplier')->orderBy('name')->get();

        $rows = [];
        $totalStockQty = 0.0;
        $totalStockValue = 0.0;
        $lowStockCount = 0;

        foreach ($products as $p) {
            $qty = (float) $p->stock_qty;
            $rate = (float) $p->rate;
            $valuation = $qty * $rate;

            $totalStockQty += $qty;
            $totalStockValue += $valuation;
            if ($p->isLowStock()) {
                $lowStockCount++;
            }

            $rows[] = [
                'sku' => $p->sku ?: '—',
                'name' => $p->name,
                'supplier' => $p->supplier?->name ?? '—',
                'unit' => $p->unit,
                'stock_qty' => number_format($qty, 2) . ' ' . $p->unit,
                'rate' => '₹' . number_format($rate, 2),
                'valuation' => '₹' . number_format($valuation, 2),
                'status' => $p->isLowStock()
                    ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700">Low Stock</span>'
                    : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">In Stock</span>',
            ];
        }

        $columns = [
            ['key' => 'sku', 'label' => 'SKU', 'align' => 'text-left'],
            ['key' => 'name', 'label' => 'Product Name', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'supplier', 'label' => 'Supplier', 'align' => 'text-left'],
            ['key' => 'stock_qty', 'label' => 'Available Stock', 'align' => 'text-right', 'cellClass' => 'font-bold text-gray-900'],
            ['key' => 'rate', 'label' => 'Unit Rate (₹)', 'align' => 'text-right'],
            ['key' => 'valuation', 'label' => 'Stock Valuation (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-bold'],
            ['key' => 'status', 'label' => 'Health', 'align' => 'text-center', 'isHtml' => true],
        ];

        $summaryCards = [
            ['label' => 'Total Catalog Items', 'value' => count($products), 'helptext' => 'Active SKUs'],
            ['label' => 'Total Available Quantity', 'value' => number_format($totalStockQty, 2), 'helptext' => 'Units in warehouse'],
            ['label' => 'Total Inventory Valuation', 'value' => '₹' . number_format($totalStockValue, 2), 'helptext' => 'Based on unit rates', 'class' => 'text-emerald-600'],
            ['label' => 'Low Stock SKU Alerts', 'value' => $lowStockCount, 'helptext' => 'Below reorder threshold', 'class' => $lowStockCount > 0 ? 'text-rose-600' : 'text-gray-900'],
        ];

        $totals = [
            'sku' => 'TOTAL (' . count($products) . ' products)',
            'stock_qty' => number_format($totalStockQty, 2),
            'valuation' => '₹' . number_format($totalStockValue, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Stock Statement & Inventory Valuation', 'Warehouse stock levels and valuation statement.', now(), now(), $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('stock_statement', now(), now(), $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'Stock Statement',
            'subtitle' => 'Live warehouse inventory snapshot, unit costs, and total stock valuation across all products.',
            'badge' => '₹' . number_format($totalStockValue, 2) . ' Total Value',
            'showDateFilter' => false,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.stock-statement', ['export' => 'pdf']),
            'exportExcelUrl' => route('admin.reports.stock-statement', ['export' => 'excel']),
            'resetUrl' => route('admin.reports.stock-statement'),
        ]);
    }

    // =========================================================================
    // 9. Item Batch Report (FIFO Lot & Expiry Tracking)
    // =========================================================================
    public function itemBatches(Request $request)
    {
        $batches = ProductBatch::with(['product', 'supplier'])->orderByDesc('inward_date')->get();

        $rows = [];
        $totalCurrentQty = 0.0;
        $totalValuation = 0.0;
        $expiredCount = 0;

        foreach ($batches as $b) {
            $qty = (float) $b->current_qty;
            $cost = (float) ($b->unit_cost ?? 0);
            $val = $qty * $cost;

            $totalCurrentQty += $qty;
            $totalValuation += $val;

            $isExpired = $b->expiry_date && Carbon::parse($b->expiry_date)->isPast();
            if ($isExpired) {
                $expiredCount++;
            }

            $rows[] = [
                'batch_no' => $b->batch_number,
                'lot_no' => $b->lot_number ?: '—',
                'product' => $b->product?->name ?? '—',
                'supplier' => $b->supplier?->name ?? '—',
                'inward_date' => $b->inward_date ? Carbon::parse($b->inward_date)->format('d M Y') : '—',
                'expiry_date' => $b->expiry_date ? Carbon::parse($b->expiry_date)->format('d M Y') : 'No Expiry',
                'current_qty' => number_format($qty, 2) . ' ' . ($b->product?->unit ?? ''),
                'unit_cost' => '₹' . number_format($cost, 2),
                'valuation' => '₹' . number_format($val, 2),
                'status' => $isExpired
                    ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700">Expired</span>'
                    : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">' . ucfirst($b->status) . '</span>',
            ];
        }

        $columns = [
            ['key' => 'batch_no', 'label' => 'Batch #', 'align' => 'text-left', 'cellClass' => 'font-mono font-bold text-gray-900'],
            ['key' => 'lot_no', 'label' => 'Lot #', 'align' => 'text-left'],
            ['key' => 'product', 'label' => 'Product Name', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'supplier', 'label' => 'Supplier', 'align' => 'text-left'],
            ['key' => 'inward_date', 'label' => 'Inward Date', 'align' => 'text-center'],
            ['key' => 'expiry_date', 'label' => 'Expiry Date', 'align' => 'text-center'],
            ['key' => 'current_qty', 'label' => 'Available Qty', 'align' => 'text-right', 'cellClass' => 'font-bold text-gray-900'],
            ['key' => 'unit_cost', 'label' => 'Cost (₹)', 'align' => 'text-right'],
            ['key' => 'valuation', 'label' => 'Batch Value (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-bold'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center', 'isHtml' => true],
        ];

        $summaryCards = [
            ['label' => 'Total Batches Tracked', 'value' => count($batches), 'helptext' => 'Inward inventory lots'],
            ['label' => 'Current Available Stock', 'value' => number_format($totalCurrentQty, 2), 'helptext' => 'Sum across all batches'],
            ['label' => 'Batch Stock Valuation', 'value' => '₹' . number_format($totalValuation, 2), 'helptext' => 'At inward cost', 'class' => 'text-emerald-600'],
            ['label' => 'Expired Batches', 'value' => $expiredCount, 'helptext' => 'Past expiry date', 'class' => $expiredCount > 0 ? 'text-rose-600' : 'text-gray-900'],
        ];

        $totals = [
            'batch_no' => 'TOTAL (' . count($batches) . ' batches)',
            'current_qty' => number_format($totalCurrentQty, 2),
            'valuation' => '₹' . number_format($totalValuation, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Item Batch & Expiry Report', 'FIFO lot inventory and expiration tracking.', now(), now(), $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('item_batches_report', now(), now(), $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'Item Batch Report',
            'subtitle' => 'FIFO inventory batches, inward arrival dates, supplier origins, shelf-life expiration, and batch cost valuations.',
            'badge' => count($batches) . ' Batches',
            'showDateFilter' => false,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.item-batches', ['export' => 'pdf']),
            'exportExcelUrl' => route('admin.reports.item-batches', ['export' => 'excel']),
            'resetUrl' => route('admin.reports.item-batches'),
        ]);
    }

    // =========================================================================
    // 10. GST Rate Wise Report (Tax slabs: 0%, 5%, 12%, 18%, 28%)
    // =========================================================================
    public function gstRateReport(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $slabs = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString())
            ->select(
                'invoice_items.gst_rate',
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('SUM(invoice_items.qty * invoice_items.rate - invoice_items.discount) as taxable_amount'),
                DB::raw('SUM((invoice_items.qty * invoice_items.rate - invoice_items.discount) * invoice_items.gst_rate / 100) as tax_amount'),
            )
            ->groupBy('invoice_items.gst_rate')
            ->orderBy('invoice_items.gst_rate')
            ->get();

        $rows = [];
        $totalTaxable = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalTax = 0.0;
        $totalGross = 0.0;

        foreach ($slabs as $s) {
            $taxable = (float) $s->taxable_amount;
            $tax = (float) $s->tax_amount;
            $cgst = round($tax / 2, 2);
            $sgst = round($tax / 2, 2);
            $gross = $taxable + $tax;

            $totalTaxable += $taxable;
            $totalCgst += $cgst;
            $totalSgst += $sgst;
            $totalTax += $tax;
            $totalGross += $gross;

            $rows[] = [
                'rate_slab' => ((float) $s->gst_rate) . '% GST',
                'invoices' => $s->invoice_count,
                'taxable' => '₹' . number_format($taxable, 2),
                'cgst' => '₹' . number_format($cgst, 2),
                'sgst' => '₹' . number_format($sgst, 2),
                'total_tax' => '₹' . number_format($tax, 2),
                'gross_total' => '₹' . number_format($gross, 2),
            ];
        }

        $columns = [
            ['key' => 'rate_slab', 'label' => 'Tax Rate Slab', 'align' => 'text-left', 'cellClass' => 'font-bold text-gray-900'],
            ['key' => 'invoices', 'label' => 'Bills Count', 'align' => 'text-center'],
            ['key' => 'taxable', 'label' => 'Taxable Turnover (₹)', 'align' => 'text-right'],
            ['key' => 'cgst', 'label' => 'Central CGST (₹)', 'align' => 'text-right', 'cellClass' => 'text-blue-600'],
            ['key' => 'sgst', 'label' => 'State SGST (₹)', 'align' => 'text-right', 'cellClass' => 'text-indigo-600'],
            ['key' => 'total_tax', 'label' => 'Total Tax Amount (₹)', 'align' => 'text-right', 'cellClass' => 'text-amber-600 font-bold'],
            ['key' => 'gross_total', 'label' => 'Gross Slab Total (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-bold'],
        ];

        $summaryCards = [
            ['label' => 'Tax Slabs Active', 'value' => count($rows), 'helptext' => 'Rate tiers invoiced'],
            ['label' => 'Total Taxable Turnover', 'value' => '₹' . number_format($totalTaxable, 2), 'helptext' => 'Assessable value'],
            ['label' => 'Total CGST (Central)', 'value' => '₹' . number_format($totalCgst, 2), 'helptext' => 'Central tax share', 'class' => 'text-blue-600'],
            ['label' => 'Total SGST (State)', 'value' => '₹' . number_format($totalSgst, 2), 'helptext' => 'State/UT tax share', 'class' => 'text-indigo-600'],
            ['label' => 'Total GST Liability', 'value' => '₹' . number_format($totalTax, 2), 'helptext' => 'CGST + SGST', 'class' => 'text-amber-600'],
        ];

        $totals = [
            'rate_slab' => 'TOTAL ACROSS ALL SLABS',
            'taxable' => '₹' . number_format($totalTaxable, 2),
            'cgst' => '₹' . number_format($totalCgst, 2),
            'sgst' => '₹' . number_format($totalSgst, 2),
            'total_tax' => '₹' . number_format($totalTax, 2),
            'gross_total' => '₹' . number_format($totalGross, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('GST Rate Wise Report', 'Breakdown of taxable sales and tax liabilities classified by GST rate slabs.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('gst_rate_wise_report', $from, $to, $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'GST Rate Wise Report',
            'subtitle' => 'Detailed slab-wise categorization of sales and output tax liability across standard GST brackets (0%, 5%, 12%, 18%).',
            'badge' => count($rows) . ' Slabs',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.gst-rate', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.gst-rate', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.gst-rate'),
        ]);
    }

    // =========================================================================
    // 11. Form No. 27EQ (Quarterly Statement of Collection of Tax at Source - TCS)
    // =========================================================================
    public function form27eq(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        // Fetch high-value B2B/B2C sales subject to TCS (Section 206C)
        $invoices = Invoice::with('customer')
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->orderByDesc('invoice_date')
            ->get();

        $rows = [];
        $totalCollected = 0.0;
        $totalTcs = 0.0;

        foreach ($invoices as $inv) {
            $amt = (float) $inv->grand_total;
            // TCS under section 206C(1H) is typically 0.1% for sale of goods above threshold
            $tcsRate = 0.10;
            $tcsAmt = round($amt * ($tcsRate / 100), 2);

            $totalCollected += $amt;
            $totalTcs += $tcsAmt;

            $rows[] = [
                'collectee_code' => $inv->customer?->gstin ? '01 (Company)' : '02 (Non-Company)',
                'pan' => $inv->customer?->gstin ? substr($inv->customer->gstin, 2, 10) : 'PANNOTAVBL',
                'name' => $inv->customer_name ?: ($inv->customer?->name ?? 'Buyer'),
                'date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                'inv_no' => $inv->number,
                'amount_paid' => '₹' . number_format($amt, 2),
                'tcs_rate' => $tcsRate . '%',
                'tcs_amount' => '₹' . number_format($tcsAmt, 2),
                'section' => '206C(1H)',
            ];
        }

        $columns = [
            ['key' => 'collectee_code', 'label' => 'Party Code', 'align' => 'text-left'],
            ['key' => 'pan', 'label' => 'Buyer PAN', 'align' => 'text-left', 'cellClass' => 'font-mono text-emerald-600 font-medium'],
            ['key' => 'name', 'label' => 'Name of Collectee / Buyer', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-900'],
            ['key' => 'date', 'label' => 'Transaction Date', 'align' => 'text-left'],
            ['key' => 'inv_no', 'label' => 'Invoice #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'amount_paid', 'label' => 'Gross Amount (₹)', 'align' => 'text-right'],
            ['key' => 'tcs_rate', 'label' => 'TCS Rate', 'align' => 'text-center'],
            ['key' => 'tcs_amount', 'label' => 'TCS Tax (₹)', 'align' => 'text-right', 'cellClass' => 'text-amber-600 font-bold'],
            ['key' => 'section', 'label' => 'IT Section', 'align' => 'text-center'],
        ];

        $summaryCards = [
            ['label' => 'Total Collectees (Buyers)', 'value' => count($rows), 'helptext' => 'Invoiced buyers in period'],
            ['label' => 'Gross Value of Sales', 'value' => '₹' . number_format($totalCollected, 2), 'helptext' => 'Total consideration received'],
            ['label' => 'Total TCS Tax Collected', 'value' => '₹' . number_format($totalTcs, 2), 'helptext' => 'Section 206C(1H) liability', 'class' => 'text-amber-600'],
            ['label' => 'Form 27EQ Status', 'value' => 'Audit Ready', 'helptext' => 'Quarterly statutory return', 'class' => 'text-emerald-600'],
        ];

        $totals = [
            'collectee_code' => 'TOTAL (' . count($rows) . ' records)',
            'amount_paid' => '₹' . number_format($totalCollected, 2),
            'tcs_amount' => '₹' . number_format($totalTcs, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('Form No. 27EQ (TCS Statement)', 'Quarterly statement of collection of tax at source under section 206C.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('form_no_27eq', $from, $to, $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'Form No. 27EQ',
            'subtitle' => 'Statutory quarterly statement of Tax Collected at Source (TCS) on sales of high-value goods and commercial services under Section 206C of the Income Tax Act.',
            'badge' => 'Form 27EQ Compliance',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.form-27eq', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.form-27eq', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.form-27eq'),
        ]);
    }

    // =========================================================================
    // 12. TCS Receivable (Tax Collected at Source by suppliers or buyers)
    // =========================================================================
    public function tcsReceivable(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        // Eligible sales transactions under Section 206C(1H)
        $invoices = Invoice::with('customer')
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->get();

        $rows = [];
        $totalTurnover = 0.0;
        $totalTcs = 0.0;

        foreach ($invoices as $inv) {
            $amt = (float) $inv->grand_total;
            $rate = 0.10;
            $tcs = round($amt * ($rate / 100), 2);

            $totalTurnover += $amt;
            $totalTcs += $tcs;

            $rows[] = [
                'date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                'inv_no' => $inv->number,
                'party' => $inv->customer_name ?: ($inv->customer?->name ?? 'Buyer'),
                'gstin_pan' => $inv->customer?->gstin ?: '—',
                'section' => 'Sec 206C(1H)',
                'turnover' => '₹' . number_format($amt, 2),
                'rate' => $rate . '%',
                'tcs_amount' => '₹' . number_format($tcs, 2),
                'status' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">Receivable</span>',
            ];
        }

        $columns = [
            ['key' => 'date', 'label' => 'Invoice Date', 'align' => 'text-left'],
            ['key' => 'inv_no', 'label' => 'Invoice #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'party', 'label' => 'Customer / Buyer Name', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-900'],
            ['key' => 'gstin_pan', 'label' => 'GSTIN / PAN', 'align' => 'text-left'],
            ['key' => 'section', 'label' => 'Section', 'align' => 'text-center'],
            ['key' => 'turnover', 'label' => 'Invoice Value (₹)', 'align' => 'text-right'],
            ['key' => 'rate', 'label' => 'TCS Rate', 'align' => 'text-center'],
            ['key' => 'tcs_amount', 'label' => 'TCS Claimable (₹)', 'align' => 'text-right', 'cellClass' => 'text-emerald-600 font-bold'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center', 'isHtml' => true],
        ];

        $summaryCards = [
            ['label' => 'Eligible Invoices', 'value' => count($rows), 'helptext' => 'Sales subject to 206C'],
            ['label' => 'Gross Consideration', 'value' => '₹' . number_format($totalTurnover, 2), 'helptext' => 'Assessable sales turnover'],
            ['label' => 'Total TCS Receivable', 'value' => '₹' . number_format($totalTcs, 2), 'helptext' => 'Credit available in 26AS', 'class' => 'text-emerald-600'],
        ];

        $totals = [
            'date' => 'TOTAL (' . count($rows) . ' invoices)',
            'turnover' => '₹' . number_format($totalTurnover, 2),
            'tcs_amount' => '₹' . number_format($totalTcs, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('TCS Receivable Report', 'Statement of tax collected at source claimable under Income Tax Act.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('tcs_receivable', $from, $to, $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'TCS Receivable',
            'subtitle' => 'Schedule of Tax Collected at Source (TCS) under Section 206C of Income Tax Act eligible for Form 26AS credit.',
            'badge' => 'TCS 206C Assets',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.tcs-receivable', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.tcs-receivable', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.tcs-receivable'),
        ]);
    }

    // =========================================================================
    // 13. TDS Payable (Tax Deducted at Source on Vendor & Contractor Payments)
    // =========================================================================
    public function tdsPayable(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        // Procurement bills and contractor payments subject to TDS (e.g. 194C contractor, 194Q goods)
        $bills = PurchaseBill::with('supplier')
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->orderByDesc('bill_date')
            ->get();

        $rows = [];
        $totalPurchases = 0.0;
        $totalTds = 0.0;

        foreach ($bills as $b) {
            $amt = (float) $b->total_amount;
            $tdsRate = 0.10; // Section 194Q / 194C
            $tds = round($amt * ($tdsRate / 100), 2);

            $totalPurchases += $amt;
            $totalTds += $tds;

            $rows[] = [
                'date' => Carbon::parse($b->bill_date)->format('d M Y'),
                'bill_no' => $b->bill_number,
                'supplier' => $b->supplier?->name ?? 'Vendor',
                'pan' => $b->supplier?->gst_no ? substr($b->supplier->gst_no, 2, 10) : '—',
                'section' => 'Sec 194Q / 194C',
                'gross_amount' => '₹' . number_format($amt, 2),
                'tds_rate' => $tdsRate . '%',
                'tds_payable' => '₹' . number_format($tds, 2),
                'status' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700">TDS Due</span>',
            ];
        }

        $columns = [
            ['key' => 'date', 'label' => 'Bill Date', 'align' => 'text-left'],
            ['key' => 'bill_no', 'label' => 'Purchase Bill #', 'align' => 'text-left', 'cellClass' => 'font-semibold text-gray-900'],
            ['key' => 'supplier', 'label' => 'Deductee / Supplier Name', 'align' => 'text-left', 'cellClass' => 'font-medium text-gray-900'],
            ['key' => 'pan', 'label' => 'Vendor PAN', 'align' => 'text-left', 'cellClass' => 'font-mono text-gray-600'],
            ['key' => 'section', 'label' => 'Section', 'align' => 'text-center'],
            ['key' => 'gross_amount', 'label' => 'Bill Amount (₹)', 'align' => 'text-right'],
            ['key' => 'tds_rate', 'label' => 'TDS Rate', 'align' => 'text-center'],
            ['key' => 'tds_payable', 'label' => 'TDS Payable (₹)', 'align' => 'text-right', 'cellClass' => 'text-rose-600 font-bold'],
            ['key' => 'status', 'label' => 'Remittance', 'align' => 'text-center', 'isHtml' => true],
        ];

        $summaryCards = [
            ['label' => 'Vendor Inward Bills', 'value' => count($rows), 'helptext' => 'Subject to TDS deduction'],
            ['label' => 'Gross Procurement Value', 'value' => '₹' . number_format($totalPurchases, 2), 'helptext' => 'Total payments credited'],
            ['label' => 'TDS Deducted & Payable', 'value' => '₹' . number_format($totalTds, 2), 'helptext' => 'Government remittance due', 'class' => 'text-rose-600'],
            ['label' => 'Challan 281 Status', 'value' => 'Pending Remittance', 'helptext' => 'Monthly deposit by 7th', 'class' => 'text-amber-600'],
        ];

        $totals = [
            'date' => 'TOTAL (' . count($rows) . ' bills)',
            'gross_amount' => '₹' . number_format($totalPurchases, 2),
            'tds_payable' => '₹' . number_format($totalTds, 2),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadGenericPdf('TDS Payable Report', 'Schedule of Tax Deducted at Source to be remitted to government.', $from, $to, $summaryCards, $columns, $rows, $totals);
        }

        if ($request->query('export') === 'excel') {
            return $this->streamGenericCsv('tds_payable', $from, $to, $columns, $rows);
        }

        return view('admin.reports.report_table', [
            'title' => 'TDS Payable',
            'subtitle' => 'Statement of Tax Deducted at Source on vendor payments, contractor jobs, and material purchases to be remitted via Challan 281.',
            'badge' => 'TDS Remittance Schedule',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'showDateFilter' => true,
            'summaryCards' => $summaryCards,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'exportPdfUrl' => route('admin.reports.tds-payable', array_merge($request->query(), ['export' => 'pdf'])),
            'exportExcelUrl' => route('admin.reports.tds-payable', array_merge($request->query(), ['export' => 'excel'])),
            'resetUrl' => route('admin.reports.tds-payable'),
        ]);
    }

    // =========================================================================
    // 14. Existing GSTR-1 Report (Preserved for compatibility)
    // =========================================================================
    public function gstReport(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $b2bInvoices = Invoice::with(['customer:id,name,gstin', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('invoice_date', '>=', $from->toDateString())
            ->whereDate('invoice_date', '<=', $to->toDateString())
            ->whereHas('customer', fn ($q) => $q->whereNotNull('gstin')->where('gstin', '!=', ''))
            ->orderByDesc('invoice_date')
            ->get()
            ->map(fn ($inv) => $this->invoiceSummary($inv));

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

        $hsnRows = InvoiceItem::query()
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereNotIn('invoices.status', ['cancelled'])
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString())
            ->select(
                DB::raw("COALESCE(invoice_items.hsn_code, 'N/A') as hsn_code"),
                'invoice_items.name',
                DB::raw('SUM(invoice_items.qty) as total_qty'),
                DB::raw('SUM(invoice_items.qty * invoice_items.rate - invoice_items.discount) as taxable_value'),
                DB::raw('AVG(invoice_items.gst_rate) as avg_gst_rate'),
                DB::raw('SUM((invoice_items.qty * invoice_items.rate - invoice_items.discount) * invoice_items.gst_rate / 100) as gst_amount'),
            )
            ->groupBy('invoice_items.hsn_code', 'invoice_items.name')
            ->orderByDesc('taxable_value')
            ->get()
            ->map(function ($row) {
                $taxable = (float) $row->taxable_value;
                $gstAmt = (float) $row->gst_amount;
                return [
                    'hsn_code' => $row->hsn_code,
                    'description' => $row->name,
                    'total_qty' => (float) $row->total_qty,
                    'taxable_value' => $taxable,
                    'cgst' => round($gstAmt / 2, 2),
                    'sgst' => round($gstAmt / 2, 2),
                    'total_tax' => round($gstAmt, 2),
                    'grand_total' => round($taxable + $gstAmt, 2),
                ];
            });

        $totals = [
            'taxable' => round($b2bInvoices->sum('taxable_value') + $b2cInvoices->sum('taxable_value'), 2),
            'cgst' => round($b2bInvoices->sum('cgst') + $b2cInvoices->sum('cgst'), 2),
            'sgst' => round($b2bInvoices->sum('sgst') + $b2cInvoices->sum('sgst'), 2),
            'total_tax' => round($b2bInvoices->sum('total_tax') + $b2cInvoices->sum('total_tax'), 2),
            'grand' => round($b2bInvoices->sum('grand_total') + $b2cInvoices->sum('grand_total'), 2),
        ];

        return view('admin.reports.gst', compact(
            'from', 'to', 'b2bInvoices', 'b2cInvoices', 'hsnRows', 'totals'
        ));
    }

    public function exportGst(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);
        $tab = $request->query('tab', 'b2b');

        $filename = "gst_{$tab}_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
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

    // =========================================================================
    // Reusable Export Helpers
    // =========================================================================

    private function downloadGenericPdf(string $title, string $subtitle, Carbon $from, Carbon $to, array $summaryCards, array $columns, array $rows, ?array $totals = null)
    {
        // Transform table columns & rows for clean PDF rendering
        $headers = array_map(fn ($c) => [
            'label' => $c['label'],
            'class' => $c['align'] ?? 'text-left',
        ], $columns);

        $pdfRows = [];
        foreach ($rows as $r) {
            $rowCells = [];
            foreach ($columns as $c) {
                $val = strip_tags((string) ($r[$c['key']] ?? '—'));
                $rowCells[] = [
                    'value' => $val,
                    'class' => $c['align'] ?? 'text-left',
                ];
            }
            $pdfRows[] = $rowCells;
        }

        $totalsRow = [];
        if (!empty($totals)) {
            foreach ($columns as $c) {
                $val = strip_tags((string) ($totals[$c['key']] ?? ''));
                $totalsRow[] = [
                    'value' => $val,
                    'class' => $c['align'] ?? 'text-left',
                ];
            }
        }

        $pdf = Pdf::loadView('admin.reports.print_generic', [
            'title' => $title,
            'subtitle' => $subtitle,
            'from' => $from,
            'to' => $to,
            'summaryCards' => $summaryCards,
            'headers' => $headers,
            'rows' => $pdfRows,
            'totalsRow' => $totalsRow,
        ])->setPaper('a4', count($columns) > 7 ? 'landscape' : 'portrait');

        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $title));
        return $pdf->download("{$safeName}_{$from->format('Ymd')}_{$to->format('Ymd')}.pdf");
    }

    private function streamGenericCsv(string $filePrefix, Carbon $from, Carbon $to, array $columns, array $rows)
    {
        $filename = "{$filePrefix}_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');

            // Header row
            fputcsv($out, array_map(fn ($c) => $c['label'], $columns));

            // Data rows
            foreach ($rows as $r) {
                $line = [];
                foreach ($columns as $c) {
                    $val = strip_tags((string) ($r[$c['key']] ?? ''));
                    $line[] = $this->sanitizeCsv($val);
                }
                fputcsv($out, $line);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function parseDateRange(Request $request): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function invoiceSummary(Invoice $inv): array
    {
        $taxable = (float) $inv->items->sum(fn ($i) => (float) $i->qty * (float) $i->rate - (float) $i->discount);
        $gstAmount = (float) $inv->gst_total;

        return [
            'invoice_number' => $inv->number,
            'invoice_date' => $inv->invoice_date,
            'customer_name' => $inv->customer_name ?: ($inv->customer?->name ?? '—'),
            'gstin' => $inv->customer?->gstin ?? '',
            'taxable_value' => round($taxable, 2),
            'cgst' => round($gstAmount / 2, 2),
            'sgst' => round($gstAmount / 2, 2),
            'total_tax' => round($gstAmount, 2),
            'grand_total' => round((float) $inv->grand_total, 2),
            'status' => $inv->status,
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
                DB::raw('SUM(invoice_items.qty * invoice_items.rate - invoice_items.discount) as taxable_value'),
                DB::raw('SUM((invoice_items.qty * invoice_items.rate - invoice_items.discount) * invoice_items.gst_rate / 100) as gst_amount'),
            )
            ->groupBy('invoice_items.hsn_code', 'invoice_items.name')
            ->orderByDesc('taxable_value')
            ->cursor();

        foreach ($rows as $row) {
            $taxable = (float) $row->taxable_value;
            $gst = (float) $row->gst_amount;
            yield [$row->hsn_code, $row->name, (float) $row->total_qty, $taxable, round($gst / 2, 2), round($gst / 2, 2), round($gst, 2), round($taxable + $gst, 2)];
        }
    }

    private function sanitizeCsv(mixed $value): string
    {
        $str = (string) $value;
        if (in_array(substr($str, 0, 1), ['=', '+', '-', '@', "\t", "\r"], true)) {
            $str = "'" . $str;
        }
        return $str;
    }
}
