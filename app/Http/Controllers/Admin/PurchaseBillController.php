<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseBillController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseBill::with(['supplier', 'createdBy', 'items.product'])->latest('bill_date');

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('contact_person', 'like', "%{$search}%");
                    });
            });
        }

        if ($supplierId = $request->query('supplier_id')) {
            if ($supplierId !== 'all') {
                $query->where('supplier_id', $supplierId);
            }
        }

        if ($status = $request->query('payment_status')) {
            if ($status !== 'all') {
                $query->where('payment_status', $status);
            }
        }

        if ($from = $request->query('from_date')) {
            $query->whereDate('bill_date', '>=', $from);
        }

        if ($to = $request->query('to_date')) {
            $query->whereDate('bill_date', '<=', $to);
        }

        $bills = $query->paginate(20)->withQueryString();

        $metrics = [
            'total_purchases' => (float) PurchaseBill::where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_paid' => (float) PurchaseBill::where('status', '!=', 'cancelled')->sum('paid_amount'),
            'total_balance_due' => (float) PurchaseBill::where('status', '!=', 'cancelled')->sum('balance_due'),
            'bills_this_month' => PurchaseBill::whereMonth('bill_date', now()->month)->whereYear('bill_date', now()->year)->count(),
        ];

        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.purchase_bills.index', compact('bills', 'metrics', 'suppliers'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()
            ->with(['batches' => function ($q) {
                $q->whereNotNull('unit_cost')->latest()->limit(1);
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'rate', 'selling_price', 'gst_rate', 'stock_qty'])
            ->map(function ($p) {
                $latestBatch = $p->batches->first();
                $p->cost_price = $latestBatch ? (float) $latestBatch->unit_cost : (float) ($p->rate ?? 0);
                return $p;
            });
        $preselectedSupplierId = $request->query('supplier_id');

        $nextBillNumber = PurchaseBill::nextBillNumber();

        return view('admin.purchase_bills.create', compact('suppliers', 'products', 'preselectedSupplierId', 'nextBillNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'supplier_invoice_no' => ['nullable', 'string', 'max:100'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.mfg_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            // Optional initial payment
            'initial_payment' => ['nullable', 'boolean'],
            'initial_payment_amount' => ['nullable', 'exclude_unless:initial_payment,1', 'required_if:initial_payment,1', 'numeric', 'min:0.01'],
            'initial_payment_method' => ['nullable', 'exclude_unless:initial_payment,1', 'string', 'in:cash,bank_transfer,cheque,upi,neft_rtgs,other'],
            'initial_payment_reference' => ['nullable', 'exclude_unless:initial_payment,1', 'string', 'max:100'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('purchase-bills', 'public');
        }

        $bill = DB::transaction(function () use ($validated, $attachmentPath, $request) {
            $subtotal = 0;
            $taxTotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = round((float) $item['quantity'], 3);
                $unitCost = round((float) $item['unit_cost'], 2);
                $taxPercent = isset($item['tax_percent']) ? (float) $item['tax_percent'] : 0;

                $itemSubtotal = round($qty * $unitCost, 2);
                $itemTax = round($itemSubtotal * ($taxPercent / 100), 2);
                $lineTotal = round($itemSubtotal + $itemTax, 2);

                $subtotal += $itemSubtotal;
                $taxTotal += $itemTax;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'batch_number' => ! empty($item['batch_number']) ? trim($item['batch_number']) : null,
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'selling_price' => ! empty($item['selling_price']) ? round((float) $item['selling_price'], 2) : null,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $itemTax,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = isset($validated['discount']) ? round((float) $validated['discount'], 2) : 0;
            $shipping = isset($validated['shipping_cost']) ? round((float) $validated['shipping_cost'], 2) : 0;
            $grandTotal = max(0, round($subtotal + $taxTotal + $shipping - $discount, 2));

            $bill = PurchaseBill::create([
                'bill_number' => PurchaseBill::nextBillNumber(),
                'supplier_id' => $validated['supplier_id'],
                'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => 'received',
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_amount' => $taxTotal,
                'shipping_cost' => $shipping,
                'total_amount' => $grandTotal,
                'paid_amount' => 0,
                'balance_due' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
                'attachment' => $attachmentPath,
                'created_by' => auth('admin')->id(),
            ]);

            // Save line items and record stock movement & batches
            foreach ($itemsData as $data) {
                $product = Product::lockForUpdate()->findOrFail($data['product_id']);

                // Find or create product batch
                $batch = null;
                if (! empty($data['batch_number'])) {
                    $batch = ProductBatch::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'batch_number' => $data['batch_number'],
                        ],
                        [
                            'inward_date' => $bill->bill_date,
                            'mfg_date' => $data['mfg_date'],
                            'expiry_date' => $data['expiry_date'],
                            'initial_qty' => $data['quantity'],
                            'current_qty' => $data['quantity'],
                            'unit_cost' => $data['unit_cost'],
                            'selling_price' => $data['selling_price'] ?? $product->rate,
                            'supplier_id' => $bill->supplier_id,
                            'status' => ProductBatch::STATUS_ACTIVE,
                        ]
                    );

                    if (! $batch->wasRecentlyCreated) {
                        $batch->current_qty = round((float) $batch->current_qty + $data['quantity'], 3);
                        $batch->initial_qty = round((float) $batch->initial_qty + $data['quantity'], 3);
                        if ($data['unit_cost'] > 0) {
                            $batch->unit_cost = $data['unit_cost'];
                        }
                        if (! empty($data['selling_price'])) {
                            $batch->selling_price = $data['selling_price'];
                        }
                        $batch->refreshStatus();
                    }
                }

                // Create bill item
                $billItem = $bill->items()->create([
                    'product_id' => $data['product_id'],
                    'batch_id' => $batch?->id,
                    'batch_number' => $data['batch_number'],
                    'mfg_date' => $data['mfg_date'],
                    'expiry_date' => $data['expiry_date'],
                    'quantity' => $data['quantity'],
                    'unit_cost' => $data['unit_cost'],
                    'selling_price' => $data['selling_price'],
                    'tax_percent' => $data['tax_percent'],
                    'tax_amount' => $data['tax_amount'],
                    'line_total' => $data['line_total'],
                ]);

                // Update product stock and supplier
                $newStock = round((float) $product->stock_qty + $data['quantity'], 3);
                $updateData = [
                    'stock_qty' => $newStock,
                    'supplier_id' => $bill->supplier_id,
                ];
                if (! empty($data['selling_price']) && $data['selling_price'] > 0) {
                    $updateData['rate'] = $data['selling_price'];
                }
                $product->update($updateData);

                // Record stock movement (inward)
                StockMovement::create([
                    'product_id' => $product->id,
                    'batch_id' => $batch?->id,
                    'purchase_bill_id' => $bill->id,
                    'type' => 'in',
                    'quantity' => $data['quantity'],
                    'stock_after' => $newStock,
                    'unit_cost' => $data['unit_cost'],
                    'supplier_id' => $bill->supplier_id,
                    'reference' => 'Bill #' . $bill->bill_number,
                    'note' => 'Purchase Bill stock inward' . ($bill->supplier_invoice_no ? " (Inv: {$bill->supplier_invoice_no})" : ''),
                    'created_by' => auth('admin')->id(),
                ]);
            }

            // Record initial payment if requested
            if (! empty($validated['initial_payment']) && ! empty($validated['initial_payment_amount'])) {
                $payAmount = min($grandTotal, round((float) $validated['initial_payment_amount'], 2));
                if ($payAmount > 0) {
                    $bill->payments()->create([
                        'payment_number' => SupplierPayment::nextPaymentNumber(),
                        'supplier_id' => $bill->supplier_id,
                        'amount' => $payAmount,
                        'payment_date' => $bill->bill_date,
                        'payment_method' => $validated['initial_payment_method'] ?? 'bank_transfer',
                        'reference_no' => $validated['initial_payment_reference'] ?? null,
                        'notes' => 'Initial payment upon bill creation.',
                        'created_by' => auth('admin')->id(),
                    ]);

                    $bill->refreshPaymentStatus();
                }
            }

            return $bill;
        });

        return redirect()->route('admin.purchase-bills.show', $bill)
            ->with('success', "Purchase Bill {$bill->bill_number} recorded successfully. Stock has been incremented.");
    }

    public function show(PurchaseBill $purchaseBill)
    {
        $purchaseBill->load(['supplier', 'createdBy', 'items.product', 'items.batch', 'payments.createdBy', 'stockMovements']);

        return view('admin.purchase_bills.show', compact('purchaseBill'));
    }

    public function print(PurchaseBill $purchaseBill)
    {
        $purchaseBill->load(['supplier', 'createdBy', 'items.product', 'items.batch', 'payments']);

        return view('admin.purchase_bills.print', compact('purchaseBill'));
    }

    public function recordPayment(Request $request, PurchaseBill $purchaseBill)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $purchaseBill->balance_due],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,cheque,upi,neft_rtgs,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $purchaseBill) {
            $payment = $purchaseBill->payments()->create([
                'payment_number' => SupplierPayment::nextPaymentNumber(),
                'supplier_id' => $purchaseBill->supplier_id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth('admin')->id(),
            ]);

            $purchaseBill->refreshPaymentStatus();
        });

        return back()->with('success', 'Payment of ₹' . number_format($validated['amount'], 2) . ' recorded successfully.');
    }

    public function destroy(PurchaseBill $purchaseBill)
    {
        if ($purchaseBill->payments()->exists()) {
            return back()->with('error', 'Cannot delete a bill that already has recorded payments. Delete payments first.');
        }

        DB::transaction(function () use ($purchaseBill) {
            // Reverse stock
            foreach ($purchaseBill->items as $item) {
                $product = $item->product;
                if ($product) {
                    $newStock = max(0, round((float) $product->stock_qty - (float) $item->quantity, 3));
                    $product->update(['stock_qty' => $newStock]);

                    if ($item->batch) {
                        $item->batch->current_qty = max(0, round((float) $item->batch->current_qty - (float) $item->quantity, 3));
                        $item->batch->refreshStatus();
                    }

                    StockMovement::create([
                        'product_id' => $product->id,
                        'batch_id' => $item->batch_id,
                        'type' => 'out',
                        'quantity' => -$item->quantity,
                        'stock_after' => $newStock,
                        'unit_cost' => $item->unit_cost,
                        'supplier_id' => $purchaseBill->supplier_id,
                        'reference' => 'Cancelled ' . $purchaseBill->bill_number,
                        'note' => 'Purchase Bill cancelled / deleted.',
                        'created_by' => auth('admin')->id(),
                    ]);
                }
            }

            $purchaseBill->delete();
        });

        return redirect()->route('admin.purchase-bills.index')->with('success', "Purchase Bill {$purchaseBill->bill_number} has been deleted and stock reversed.");
    }
}
