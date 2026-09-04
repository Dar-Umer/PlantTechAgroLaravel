<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\InvoiceNumberer;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with('customer')->latest('invoice_date');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'totals' => [
                'count' => Invoice::count(),
                'outstanding' => (float) Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum(DB::raw('grand_total - amount_paid')),
                'collected' => (float) Invoice::sum('amount_paid'),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'unit', 'rate', 'gst_rate']);

        return view('admin.invoices.create', [
            'customers' => $customers,
            'products' => $products,
            'terms' => config('invoice.terms', ''),
            'preselectCustomer' => $request->query('customer_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);

        $invoice = DB::transaction(function () use ($data, $customer, $request) {
            $invoice = Invoice::create([
                'number' => InvoiceNumberer::next(),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'unpaid',
                'terms' => $data['terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user('admin')->id ?? null,
            ]);

            foreach ($data['items'] as $index => $row) {
                $item = $this->makeItem($invoice, $row, $index);

                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    StockService::record($product, 'out', (float) $item->qty, $invoice->number, 'Invoice sale', null, null, $request->user('admin')->id ?? null);
                }
            }

            $this->recalculate($invoice);

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice '.$invoice->number.' created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'payments', 'customer', 'workOrder']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['items', 'customer', 'workOrder']);

        return view('admin.invoices.print', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['items', 'customer', 'workOrder']);

        $pdf = Pdf::loadView('admin.invoices.print', ['invoice' => $invoice, 'forPdf' => true])
            ->setPaper('a4');

        return $pdf->download(str_replace('/', '-', $invoice->number).'.pdf');
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        if ($invoice->isCancelled()) {
            return back()->with('error', 'Payments cannot be recorded against a cancelled invoice.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$invoice->balanceDue()],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice->payments()->create($data + [
            'received_by' => $request->user('admin')->id ?? null,
        ]);

        $invoice->amount_paid = round((float) $invoice->payments()->sum('amount'), 2);
        $invoice->status = bccomp((string) $invoice->amount_paid, (string) $invoice->grand_total, 2) >= 0
            ? 'paid'
            : 'partial';
        $invoice->save();

        return back()->with('success', 'Payment of ₹'.number_format((float) $data['amount'], 2).' recorded.');
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->isCancelled()) {
            return back()->with('error', 'This invoice is already cancelled.');
        }

        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items()->whereNotNull('product_id')->get() as $item) {
                $product = Product::find($item->product_id);

                if ($product) {
                    StockService::record($product, 'in', (float) $item->qty, 'REV-'.$invoice->number, 'Stock reversed — invoice cancelled', null, null, auth('admin')->id());
                }
            }

            $invoice->update(['status' => 'cancelled']);
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice cancelled and stock reversed.');
    }

    private function makeItem(Invoice $invoice, array $row, int $index): InvoiceItem
    {
        $qty = (float) $row['qty'];
        $rate = (float) $row['rate'];
        $discount = (float) ($row['discount'] ?? 0);
        $gstRate = (float) ($row['gst_rate'] ?? 0);
        $total = max(0, round($qty * $rate - $discount, 2));

        return $invoice->items()->create([
            'product_id' => $row['product_id'] ?? null,
            'name' => $row['name'],
            'unit' => $row['unit'] ?? null,
            'qty' => $qty,
            'rate' => $rate,
            'discount' => $discount,
            'gst_rate' => $gstRate,
            'total' => $total,
            'sort_order' => $index,
        ]);
    }

    private function recalculate(Invoice $invoice): void
    {
        $invoice->refresh()->load('items');

        $subtotal = round((float) $invoice->items->sum(fn ($i) => (float) $i->qty * (float) $i->rate), 2);
        $discountTotal = round((float) $invoice->items->sum('discount'), 2);
        $gstTotal = round((float) $invoice->items->sum(fn ($i) => $i->gstAmount()), 2);
        $grandTotal = round(max(0, $subtotal - $discountTotal + $gstTotal), 2);

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'gst_total' => $gstTotal,
            'grand_total' => $grandTotal,
        ]);

        if (! $invoice->isCancelled()) {
            $invoice->amount_paid = round((float) $invoice->payments()->sum('amount'), 2);
            $invoice->status = bccomp((string) $invoice->amount_paid, (string) $grandTotal, 2) >= 0 && $grandTotal > 0
                ? 'paid'
                : 'unpaid';
            $invoice->save();
        }
    }
}
