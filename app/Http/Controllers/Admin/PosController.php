<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosSalePayment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\PosInvoiceNumberer;
use App\Services\StockService;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    /**
     * Show the Point of Sale Terminal screen.
     */
    public function terminal()
    {
        $products = Product::active()
            ->with(['activeBatchesFifo' => function ($q) {
                $q->select('id', 'product_id', 'batch_number', 'lot_number', 'current_qty', 'unit_cost', 'selling_price', 'expiry_date');
            }])
            ->orderBy('name')
            ->get();

        $customers = PosCustomer::latest()->limit(50)->get();
        $walkIn = PosCustomer::walkIn();

        return view('admin.pos.terminal', compact('products', 'customers', 'walkIn'));
    }

    /**
     * Search products for POS catalog & barcode scan.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = Product::active()->with(['activeBatchesFifo']);

        if ($q !== '') {
            $safeQ = addcslashes($q, '%_\\');
            $query->where(function ($sub) use ($safeQ) {
                $sub->where('name', 'like', "%{$safeQ}%")
                    ->orWhere('sku', 'like', "%{$safeQ}%")
                    ->orWhere('hsn_code', 'like', "%{$safeQ}%");
            });
        }

        $products = $query->orderBy('name')->limit(60)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'image_url' => \App\Support\Media::url($p->image),
                'type' => $p->type,
                'unit' => $p->unit,
                'stock' => (float) $p->stock_qty,
                'rate' => (float) ($p->selling_price ?? $p->rate),
                'cost' => (float) ($p->rate ?? 0),
                'batches' => $p->activeBatchesFifo->map(fn ($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'lot_number' => $b->lot_number,
                    'current_qty' => (float) $b->current_qty,
                    'unit_cost' => (float) ($b->unit_cost ?? 0),
                    'selling_price' => $b->effectiveSellingPrice(),
                    'expiry' => $b->expiry_date?->format('d M Y'),
                    'is_near_expiry' => $b->isNearExpiry(60),
                ])->values(),
            ];
        });

        return response()->json($products);
    }

    /**
     * Search POS customers by name, phone, or Orchardist ID.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $posCustomers = PosCustomer::query()
            ->when($q !== '', function ($query) use ($q) {
                $safeQ = addcslashes($q, '%_\\');
                $query->where(function ($sub) use ($safeQ) {
                    $sub->where('name', 'like', "%{$safeQ}%")
                        ->orWhere('phone', 'like', "%{$safeQ}%")
                        ->orWhere('gstin', 'like', "%{$safeQ}%");
                });
            })
            ->latest()
            ->limit(20)
            ->get(['id', 'customer_id', 'name', 'phone', 'email', 'gstin', 'outstanding_balance']);

        // Also search registered Orchardists from Customer model
        if ($q !== '') {
            $safeQ = addcslashes($q, '%_\\');
            $orchardists = Customer::where(function ($sub) use ($safeQ) {
                $sub->where('orchardist_id', 'like', "%{$safeQ}%")
                    ->orWhere('name', 'like', "%{$safeQ}%")
                    ->orWhere('phone', 'like', "%{$safeQ}%");
            })->limit(10)->get();

            foreach ($orchardists as $orchardist) {
                $existing = $posCustomers->first(fn ($pc) => $pc->customer_id === $orchardist->id || ($pc->phone && $pc->phone === $orchardist->phone));
                if (! $existing) {
                    $posCust = PosCustomer::firstOrCreate(
                        ['customer_id' => $orchardist->id],
                        [
                            'name' => $orchardist->name . ($orchardist->orchardist_id ? " ({$orchardist->orchardist_id})" : ''),
                            'phone' => $orchardist->phone,
                            'email' => $orchardist->email,
                            'address' => $orchardist->address,
                            'gstin' => $orchardist->gstin,
                            'outstanding_balance' => 0,
                        ]
                    );
                    $posCustomers->push($posCust);
                }
            }
        }

        return response()->json($posCustomers->values());
    }

    /**
     * Quick-add a new POS customer during billing.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $data['created_by'] = $request->user('admin')?->id;
        $data['outstanding_balance'] = 0;

        $customer = PosCustomer::create($data);

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    /**
     * Record payment towards customer's outstanding balance.
     */
    public function settleBalance(Request $request, PosCustomer $customer): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,card,bank_transfer,other'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = min((float) $validated['amount'], (float) $customer->outstanding_balance);
        $customer->decrement('outstanding_balance', $amount);
        $customer->increment('total_spent', $amount);

        return response()->json([
            'success' => true,
            'message' => 'Payment of ₹' . number_format($amount, 2) . ' recorded against ' . $customer->name . "'s balance.",
            'new_balance' => (float) $customer->refresh()->outstanding_balance,
        ]);
    }

    /**
     * Process POS Checkout and create Sale / Invoice with Multi-Batch & Split Payment support.
     * Note: Per requirements, NO taxes are included in POS transactions.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:pos_customers,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_gstin' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_id' => ['nullable', 'exists:product_batches,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:fixed,percent'],
            'payment_method' => ['required', 'in:cash,upi,card,bank_transfer,split,other'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required_with:payments', 'string', 'in:cash,upi,card,bank_transfer,other'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.reference' => ['nullable', 'string', 'max:100'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $invoiceNumber = PosInvoiceNumberer::next();
            $adminId = $request->user('admin')?->id;

            $subtotal = 0;
            $itemsToCreate = [];

            // Pre-validate stock and prepare allocations
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $discount = (float) ($itemData['discount'] ?? 0);
                $batchId = $itemData['batch_id'] ?? null;

                if ((float) $product->stock_qty < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->name}. In stock: " . Format::qty($product->stock_qty) . " {$product->unit}.",
                    ]);
                }

                if ($batchId) {
                    $selectedBatch = ProductBatch::where('product_id', $product->id)->find($batchId);
                    if ($selectedBatch && (float) $selectedBatch->current_qty < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock in batch {$selectedBatch->batch_number} for {$product->name}. Available in batch: " . Format::qty($selectedBatch->current_qty) . " {$product->unit}.",
                        ]);
                    }
                }

                // Strictly NO taxes in POS transactions
                $lineSubtotal = round($price * $qty, 2) - $discount;
                $lineTotal = round($lineSubtotal, 2);

                $subtotal += $lineSubtotal;

                // Determine batch cost and deduct stock
                $allocations = $batchId
                    ? [['batch_id' => $batchId, 'unit_cost' => (float) ProductBatch::find($batchId)?->unit_cost, 'quantity' => $qty]]
                    : StockService::allocateFifo($product, $qty);

                // Deduct stock using StockService
                StockService::record(
                    product: $product,
                    type: 'out',
                    quantity: $qty,
                    reference: $invoiceNumber,
                    note: "POS Sale #{$invoiceNumber}",
                    userId: $adminId,
                    batchId: $batchId,
                );

                $primaryCost = ! empty($allocations) ? ($allocations[0]['unit_cost'] ?? $product->rate) : $product->rate;
                $primaryBatchId = ! empty($allocations) ? ($allocations[0]['batch_id'] ?? null) : $batchId;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'batch_id' => $primaryBatchId,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'unit_price' => $price,
                    'cost_price' => $primaryCost,
                    'quantity' => $qty,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => $discount,
                    'total_price' => $lineTotal,
                ];
            }

            $orderDiscount = (float) ($validated['discount_amount'] ?? 0);
            $rawGrandTotal = max(0, $subtotal - $orderDiscount);
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            // Compute split payments / single payment
            $payments = [];
            $amountPaid = 0;
            $amountTendered = (float) ($validated['amount_tendered'] ?? 0);

            if ($validated['payment_method'] === 'split' && ! empty($validated['payments'])) {
                foreach ($validated['payments'] as $p) {
                    $amt = (float) ($p['amount'] ?? 0);
                    if ($amt > 0) {
                        $payments[] = [
                            'method' => $p['method'],
                            'amount' => $amt,
                            'reference' => $p['reference'] ?? null,
                        ];
                        $amountPaid += $amt;
                        if ($p['method'] === 'cash' && $amountTendered <= 0) {
                            $amountTendered += $amt;
                        }
                    }
                }
            } else {
                $method = $validated['payment_method'];
                $explicitPaid = isset($validated['amount_paid']) ? (float) $validated['amount_paid'] : null;

                if ($method === 'cash') {
                    $tenderedVal = $amountTendered > 0 ? $amountTendered : $roundedGrandTotal;
                    $amountTendered = $tenderedVal;
                    $amountPaid = $explicitPaid !== null ? min($explicitPaid, $roundedGrandTotal) : min($tenderedVal, $roundedGrandTotal);
                } else {
                    $amountPaid = $explicitPaid !== null ? min($explicitPaid, $roundedGrandTotal) : $roundedGrandTotal;
                    $amountTendered = $amountPaid;
                }

                if ($amountPaid > 0) {
                    $payments[] = [
                        'method' => $method,
                        'amount' => $amountPaid,
                        'reference' => null,
                    ];
                }
            }

            $amountPaid = round(min($amountPaid, $roundedGrandTotal), 2);
            $balanceDue = max(0, round($roundedGrandTotal - $amountPaid, 2));
            $change = max(0, round($amountTendered - $amountPaid, 2));

            $paymentStatus = $balanceDue <= 0
                ? PosSale::PAYMENT_STATUS_PAID
                : ($amountPaid > 0 ? PosSale::PAYMENT_STATUS_PARTIAL : PosSale::PAYMENT_STATUS_UNPAID);

            // If there is an unpaid balance, ensure customer is not anonymous Walk-in
            $customerId = $validated['customer_id'] ?? null;
            if ($balanceDue > 0 && empty($customerId)) {
                if (! empty($validated['customer_phone']) || ($validated['customer_name'] !== 'Walk-in Customer' && ! empty($validated['customer_name']))) {
                    $posCust = PosCustomer::create([
                        'name' => $validated['customer_name'],
                        'phone' => $validated['customer_phone'] ?? null,
                        'created_by' => $adminId,
                        'outstanding_balance' => 0,
                    ]);
                    $customerId = $posCust->id;
                } else {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Please select or add a named customer to record an outstanding balance of ₹' . number_format($balanceDue, 2) . '.',
                    ]);
                }
            }

            $sale = PosSale::create([
                'invoice_number' => $invoiceNumber,
                'pos_customer_id' => $customerId,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_gstin' => $validated['customer_gstin'] ?? null,
                'sale_date' => now(),
                'status' => PosSale::STATUS_COMPLETED,
                'subtotal' => $subtotal,
                'discount_amount' => $orderDiscount,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'gst_amount' => 0,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'],
                'amount_tendered' => $amountTendered,
                'change_amount' => $change,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $adminId,
            ]);

            foreach ($itemsToCreate as $item) {
                $sale->items()->create($item);
            }

            foreach ($payments as $pay) {
                $sale->payments()->create($pay);
            }

            // Update POS customer stats & outstanding balance
            if (! empty($customerId)) {
                $posCust = PosCustomer::find($customerId);
                if ($posCust) {
                    $posCust->increment('orders_count');
                    $posCust->increment('total_spent', $amountPaid);
                    if ($balanceDue > 0) {
                        $posCust->increment('outstanding_balance', $balanceDue);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'grand_total' => $roundedGrandTotal,
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
                'payment_status' => $paymentStatus,
                'receipt_url' => route('admin.pos.receipt', $sale),
                'invoice_url' => route('admin.pos.invoice', $sale),
                'message' => "Sale completed successfully! Invoice: {$invoiceNumber}",
            ]);
        });
    }

    /**
     * Sales history & POS invoices list.
     */
    public function sales(Request $request)
    {
        $query = PosSale::query()->with(['customer', 'cashier', 'items.product', 'payments'])->latest('sale_date');

        if ($search = trim((string) $request->query('q', ''))) {
            $safeQ = addcslashes($search, '%_\\');
            $query->where(function ($sub) use ($safeQ) {
                $sub->where('invoice_number', 'like', "%{$safeQ}%")
                    ->orWhere('customer_name', 'like', "%{$safeQ}%")
                    ->orWhere('customer_phone', 'like', "%{$safeQ}%");
            });
        }

        if ($method = $request->query('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($pStatus = $request->query('payment_status')) {
            $query->where('payment_status', $pStatus);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('sale_date', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('sale_date', '<=', $to);
        }

        $sales = $query->paginate(25)->withQueryString();

        // KPIs
        $today = now()->startOfDay();
        $todaySales = (float) PosSale::where('status', PosSale::STATUS_COMPLETED)->where('sale_date', '>=', $today)->sum('grand_total');
        $todayOrders = PosSale::where('status', PosSale::STATUS_COMPLETED)->where('sale_date', '>=', $today)->count();
        $cashSales = (float) PosSale::where('status', PosSale::STATUS_COMPLETED)->where('sale_date', '>=', $today)->where('payment_method', 'cash')->sum('amount_paid');
        $upiSales = (float) PosSale::where('status', PosSale::STATUS_COMPLETED)->where('sale_date', '>=', $today)->where('payment_method', 'upi')->sum('amount_paid');
        $totalDue = (float) PosSale::where('status', PosSale::STATUS_COMPLETED)->sum('balance_due');

        return view('admin.pos.sales', compact(
            'sales', 'todaySales', 'todayOrders', 'cashSales', 'upiSales', 'totalDue'
        ));
    }

    /**
     * View POS Sale details.
     */
    public function show(PosSale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product', 'items.batch', 'payments']);

        return view('admin.pos.show', compact('sale'));
    }

    /**
     * 80mm Thermal Receipt format for receipt printers (Zero tax format).
     */
    public function receipt(PosSale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product', 'payments']);
        $settings = app(\App\Services\ShopSettingsService::class)->all();

        return view('admin.pos.receipt', compact('sale', 'settings'));
    }

    /**
     * Full A4 Retail Invoice / Cash Bill for POS Sale (Zero tax format).
     */
    public function invoice(PosSale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product', 'items.batch', 'payments']);
        $settings = app(\App\Services\ShopSettingsService::class)->all();

        return view('admin.pos.invoice', compact('sale', 'settings'));
    }

    /**
     * Cancel POS Sale and reverse deducted stock and customer balance.
     */
    public function cancel(Request $request, PosSale $sale)
    {
        if ($sale->isCancelled()) {
            return back()->with('error', 'Sale is already cancelled.');
        }

        DB::transaction(function () use ($sale, $request) {
            $adminId = $request->user('admin')?->id;

            // Reverse stock for each item
            foreach ($sale->items as $item) {
                if ($item->product) {
                    StockService::record(
                        product: $item->product,
                        type: 'in',
                        quantity: (float) $item->quantity,
                        reference: "REV-{$sale->invoice_number}",
                        note: "Reversal of cancelled POS Sale #{$sale->invoice_number}",
                        userId: $adminId,
                        batchId: $item->batch_id,
                        unitCost: $item->cost_price
                    );
                }
            }

            // Reverse customer spend and balance
            if ($sale->pos_customer_id) {
                $posCust = PosCustomer::find($sale->pos_customer_id);
                if ($posCust) {
                    $posCust->decrement('orders_count');
                    $posCust->decrement('total_spent', min((float) $posCust->total_spent, (float) $sale->amount_paid));
                    if ((float) $sale->balance_due > 0) {
                        $posCust->decrement('outstanding_balance', min((float) $posCust->outstanding_balance, (float) $sale->balance_due));
                    }
                }
            }

            $sale->update(['status' => PosSale::STATUS_CANCELLED]);
        });

        return back()->with('success', "Sale #{$sale->invoice_number} has been cancelled and stock returned to inventory.");
    }
}
