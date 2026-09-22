<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\PosSaleItem;
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
                $q->select('id', 'product_id', 'batch_number', 'lot_number', 'current_qty', 'unit_cost', 'expiry_date');
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
                'unit' => $p->unit,
                'stock' => (float) $p->stock_qty,
                'rate' => (float) ($p->selling_price ?? $p->rate),
                'cost' => (float) ($p->rate ?? 0),
                'gst_rate' => (float) ($p->gst_rate ?? 0),
                'batches' => $p->activeBatchesFifo->map(fn ($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'current_qty' => (float) $b->current_qty,
                    'unit_cost' => (float) ($b->unit_cost ?? 0),
                    'expiry' => $b->expiry_date?->format('d M Y'),
                ])->values(),
            ];
        });

        return response()->json($products);
    }

    /**
     * Search POS customers by name or phone.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $customers = PosCustomer::query()
            ->when($q !== '', function ($query) use ($q) {
                $safeQ = addcslashes($q, '%_\\');
                $query->where('name', 'like', "%{$safeQ}%")
                    ->orWhere('phone', 'like', "%{$safeQ}%");
            })
            ->latest()
            ->limit(20)
            ->get(['id', 'name', 'phone', 'email', 'gstin']);

        return response()->json($customers);
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

        $customer = PosCustomer::create($data);

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    /**
     * Process POS Checkout and create Sale / Invoice with FIFO stock deduction.
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
            'payment_method' => ['required', 'in:cash,upi,card,bank_transfer,other'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $invoiceNumber = PosInvoiceNumberer::next();
            $adminId = $request->user('admin')?->id;

            $subtotal = 0;
            $totalTax = 0;
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

                $taxRate = (float) ($product->gst_rate ?? 0);
                $lineSubtotal = round($price * $qty, 2) - $discount;
                $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                // Determine batch cost and deduct stock
                $allocations = $batchId
                    ? [['batch_id' => $batchId, 'unit_cost' => (float) ProductBatch::find($batchId)?->unit_cost, 'quantity' => $qty]]
                    : StockService::allocateFifo($product, $qty);

                // Deduct stock using StockService (which natively handles FIFO if no batch is supplied)
                StockService::record(
                    product: $product,
                    type: 'out',
                    quantity: $qty,
                    reference: $invoiceNumber,
                    note: "POS Sale #{$invoiceNumber}",
                    userId: $adminId,
                    batchId: $batchId,
                );

                // For line item cost tracking
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
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'discount_amount' => $discount,
                    'total_price' => $lineTotal,
                ];
            }

            $orderDiscount = (float) ($validated['discount_amount'] ?? 0);
            $rawGrandTotal = max(0, $subtotal + $totalTax - $orderDiscount);
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            $tendered = (float) ($validated['amount_tendered'] ?? $roundedGrandTotal);
            $change = max(0, round($tendered - $roundedGrandTotal, 2));

            $sale = PosSale::create([
                'invoice_number' => $invoiceNumber,
                'pos_customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_gstin' => $validated['customer_gstin'] ?? null,
                'sale_date' => now(),
                'status' => PosSale::STATUS_COMPLETED,
                'subtotal' => $subtotal,
                'discount_amount' => $orderDiscount,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'gst_amount' => $totalTax,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'payment_method' => $validated['payment_method'],
                'amount_tendered' => $tendered,
                'change_amount' => $change,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $adminId,
            ]);

            foreach ($itemsToCreate as $item) {
                $sale->items()->create($item);
            }

            // Update POS customer stats
            if (! empty($validated['customer_id'])) {
                $posCust = PosCustomer::find($validated['customer_id']);
                if ($posCust) {
                    $posCust->increment('orders_count');
                    $posCust->increment('total_spent', $roundedGrandTotal);
                }
            }

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'grand_total' => $roundedGrandTotal,
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
        $query = PosSale::query()->with(['customer', 'cashier', 'items.product'])->latest('sale_date');

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
        $cashSales = (float) PosSale::where('status', PosSale::STATUS_COMPLETED)->where('sale_date', '>=', $today)->where('payment_method', 'cash')->sum('grand_total');
        $upiSales = (float) PosSale::where('status', PosSale::STATUS_COMPLETED)->where('sale_date', '>=', $today)->where('payment_method', 'upi')->sum('grand_total');

        return view('admin.pos.sales', compact(
            'sales', 'todaySales', 'todayOrders', 'cashSales', 'upiSales'
        ));
    }

    /**
     * View POS Sale details.
     */
    public function show(PosSale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product', 'items.batch']);

        return view('admin.pos.show', compact('sale'));
    }

    /**
     * 80mm Thermal Receipt format for receipt printers.
     */
    public function receipt(PosSale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product']);
        $settings = app(\App\Services\ShopSettingsService::class)->all();

        return view('admin.pos.receipt', compact('sale', 'settings'));
    }

    /**
     * Full A4 Tax Invoice for POS Sale.
     */
    public function invoice(PosSale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product', 'items.batch']);
        $settings = app(\App\Services\ShopSettingsService::class)->all();

        return view('admin.pos.invoice', compact('sale', 'settings'));
    }

    /**
     * Cancel POS Sale and reverse deducted stock.
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

            // Reverse customer spend
            if ($sale->pos_customer_id) {
                $posCust = PosCustomer::find($sale->pos_customer_id);
                if ($posCust) {
                    $posCust->decrement('orders_count');
                    $posCust->decrement('total_spent', min((float) $posCust->total_spent, (float) $sale->grand_total));
                }
            }

            $sale->update(['status' => PosSale::STATUS_CANCELLED]);
        });

        return back()->with('success', "Sale #{$sale->invoice_number} has been cancelled and stock returned to inventory.");
    }
}
