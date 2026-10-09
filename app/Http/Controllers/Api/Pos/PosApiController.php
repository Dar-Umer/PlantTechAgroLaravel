<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosSalePayment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Services\PosInvoiceNumberer;
use App\Services\StockService;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosApiController extends Controller
{
    /**
     * Heartbeat ping to check connection from desktop POS software.
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'store' => config('app.name', 'Plant Tech Agro'),
            'server_time' => now()->toIso8601String(),
            'database' => 'connected',
        ]);
    }

    /**
     * Cashier authentication.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($validated['login']);
        $password = $validated['password'];

        // Find admin by email or phone
        $admin = Admin::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->first();

        // Also allow matching if username is 'admin' or 'cashier'
        if (!$admin && (strtolower($loginInput) === 'admin' || strtolower($loginInput) === 'cashier')) {
            $admin = Admin::where('is_active', true)->first();
        }

        if (!$admin) {
            return response()->json([
                'message' => 'No cashier account found matching credentials.',
            ], 401);
        }

        if (!Hash::check($password, $admin->password)) {
            // Also allow test default password '1234' or 'password' in local environment
            if (!(app()->environment('local') && in_array($password, ['1234', 'password', 'admin123'], true))) {
                return response()->json([
                    'message' => 'Incorrect password entered.',
                ], 401);
            }
        }

        $token = $admin->createToken('pos-terminal')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role ?? 'Store Cashier',
            ],
        ]);
    }

    /**
     * Get active products catalog with stock and FIFO batches.
     */
    public function products(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        $query = Product::where('is_active', true)
            ->with(['activeBatchesFifo']);

        if ($q !== '') {
            $safeQ = addcslashes($q, '%_\\');
            $query->where(function ($sub) use ($safeQ) {
                $sub->where('name', 'like', "%{$safeQ}%")
                    ->orWhere('sku', 'like', "%{$safeQ}%")
                    ->orWhere('hsn_code', 'like', "%{$safeQ}%");
            });
        }

        $products = $query->orderBy('name')->get()->map(function ($p) {
            $rate = (float) ($p->selling_price ?? $p->rate);
            
            // Derive a friendly category if empty
            $cat = 'General';
            $lowerName = strtolower($p->name);
            if (str_contains($lowerName, 'plant') || str_contains($lowerName, 'm9') || str_contains($lowerName, 'sapling')) {
                $cat = 'Plants';
            } elseif (str_contains($lowerName, 'pipe') || str_contains($lowerName, 'trellis') || str_contains($lowerName, 'tool') || str_contains($lowerName, 'shear')) {
                $cat = 'Tools & Trellis';
            } elseif (str_contains($lowerName, 'fertil') || str_contains($lowerName, 'npk') || str_contains($lowerName, 'nutrient')) {
                $cat = 'Fertilizers';
            } elseif (str_contains($lowerName, 'spray') || str_contains($lowerName, 'pesticide') || str_contains($lowerName, 'fungi')) {
                $cat = 'Pesticides';
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->sku,
                'category' => $cat,
                'type' => $p->type,
                'unit' => $p->unit ?? 'pcs',
                'hsn_code' => $p->hsn_code,
                'stock_qty' => (float) $p->stock_qty,
                'rate' => $rate,
                'cost' => (float) ($p->rate ?? 0),
                'image_url' => $p->image ? asset('storage/' . $p->image) : null,
                'batches' => $p->activeBatchesFifo->map(function ($b) use ($rate) {
                    return [
                        'id' => $b->id,
                        'product_id' => $b->product_id,
                        'batch_number' => $b->batch_number,
                        'lot_number' => $b->lot_number,
                        'current_qty' => (float) $b->current_qty,
                        'unit_cost' => (float) ($b->unit_cost ?? 0),
                        'selling_price' => (float) ($b->selling_price ?? $rate),
                        'expiry_date' => $b->expiry_date?->format('Y-m-d'),
                        'is_near_expiry' => method_exists($b, 'isNearExpiry') ? $b->isNearExpiry(60) : false,
                    ];
                })->values(),
            ];
        });

        return response()->json($products);
    }

    /**
     * Get searchable customers with balance.
     */
    public function customers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = PosCustomer::query();

        if ($q !== '') {
            $safeQ = addcslashes($q, '%_\\');
            $query->where(function ($sub) use ($safeQ) {
                $sub->where('name', 'like', "%{$safeQ}%")
                    ->orWhere('phone', 'like', "%{$safeQ}%")
                    ->orWhere('address', 'like', "%{$safeQ}%");
            });
        }

        $customers = $query->orderBy('name')->limit(60)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone ?? '',
                'email' => $c->email,
                'village' => $c->address,
                'orchard_area' => $c->notes,
                'balance' => (float) ($c->outstanding_balance ?? 0),
                'total_spent' => (float) ($c->total_spent ?? 0),
                'is_walk_in' => $c->id === 1 || str_contains(strtolower($c->name), 'walk-in'),
            ];
        });

        return response()->json($customers);
    }

    /**
     * Create a new POS customer.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'village' => ['nullable', 'string', 'max:255'],
            'orchard_area' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = PosCustomer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['village'] ?? null,
            'notes' => $validated['orchard_area'] ?? null,
            'outstanding_balance' => 0,
            'total_spent' => 0,
            'orders_count' => 0,
        ]);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone ?? '',
            'village' => $customer->address,
            'orchard_area' => $customer->notes,
            'balance' => 0,
            'total_spent' => 0,
            'is_walk_in' => false,
        ]);
    }

    /**
     * Process checkout and save sale, items, and inventory deductions.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cashier_name' => ['nullable', 'string'],
            'customer' => ['nullable', 'array'],
            'customer.id' => ['nullable'],
            'customer.name' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product' => ['required', 'array'],
            'items.*.product.id' => ['required'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric'],
            'items.*.batch' => ['nullable', 'array'],
            'subtotal' => ['required', 'numeric'],
            'discount_total' => ['nullable', 'numeric'],
            'tax_total' => ['nullable', 'numeric'],
            'round_off' => ['nullable', 'numeric'],
            'grand_total' => ['required', 'numeric'],
            'payments' => ['required', 'array', 'min:1'],
            'amount_paid' => ['nullable', 'numeric'],
            'change_due' => ['nullable', 'numeric'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $customerId = $validated['customer']['id'] ?? 1;
            $customerName = $validated['customer']['name'] ?? 'Walk-in Orchardist';
            $customerPhone = $validated['customer']['phone'] ?? null;

            // Generate invoice number
            $invoiceNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $primaryPaymentMethod = $validated['payments'][0]['method'] ?? 'cash';

            // Create PosSale
            $sale = PosSale::create([
                'invoice_number' => $invoiceNumber,
                'pos_customer_id' => is_numeric($customerId) && PosCustomer::where('id', $customerId)->exists() ? $customerId : null,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'sale_date' => now(),
                'status' => 'completed',
                'subtotal' => (float) $validated['subtotal'],
                'discount_amount' => (float) ($validated['discount_total'] ?? 0),
                'discount_type' => 'fixed',
                'gst_amount' => (float) ($validated['tax_total'] ?? 0),
                'round_off' => (float) ($validated['round_off'] ?? 0),
                'grand_total' => (float) $validated['grand_total'],
                'payment_method' => $primaryPaymentMethod,
                'amount_tendered' => (float) ($validated['amount_paid'] ?? $validated['grand_total']),
                'change_amount' => (float) ($validated['change_due'] ?? 0),
                'notes' => 'Billed via Windows POS Desktop Terminal',
            ]);

            // Save payments in pos_sale_payments if table exists
            foreach ($validated['payments'] as $pay) {
                if (\Illuminate\Support\Facades\Schema::hasTable('pos_sale_payments')) {
                    PosSalePayment::create([
                        'pos_sale_id' => $sale->id,
                        'method' => $pay['method'] ?? 'cash',
                        'amount' => (float) ($pay['amount'] ?? 0),
                        'reference' => $pay['reference'] ?? null,
                    ]);
                }

                // If payment method is khata / credit, update customer balance
                if (($pay['method'] ?? '') === 'khata' && $customerId) {
                    $posCustomer = PosCustomer::find($customerId);
                    if ($posCustomer) {
                        $posCustomer->increment('outstanding_balance', (float) $pay['amount']);
                        $posCustomer->increment('total_spent', (float) $pay['amount']);
                    }
                }
            }

            // Process Items and Stock Deductions
            foreach ($validated['items'] as $item) {
                $productId = $item['product']['id'] ?? null;
                $batchId = $item['batch']['id'] ?? null;
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discount = (float) ($item['discount'] ?? 0);
                $lineTotal = round(($unitPrice - $discount) * $qty, 2);

                $product = is_numeric($productId) ? Product::find($productId) : null;

                if ($product) {
                    try {
                        StockService::record(
                            product: $product,
                            type: 'out',
                            quantity: $qty,
                            reference: $invoiceNumber,
                            note: "POS Sale #{$invoiceNumber}",
                            userId: null,
                            batchId: $batchId,
                        );
                    } catch (\Throwable $e) {
                        $product->decrement('stock_qty', $qty);
                        if ($batchId) {
                            $batch = ProductBatch::find($batchId);
                            if ($batch) {
                                $batch->decrement('current_qty', min((float) $batch->current_qty, $qty));
                            }
                        }
                    }
                }

                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'product_id' => $product?->id,
                    'batch_id' => $batchId,
                    'product_name' => $item['product']['name'] ?? 'Product',
                    'unit' => $item['product']['unit'] ?? 'pcs',
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => $discount * $qty,
                    'line_total' => $lineTotal,
                ]);
            }

            return response()->json([
                'id' => $sale->id,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $sale->sale_date->toIso8601String(),
                'cashier_name' => $validated['cashier_name'] ?? 'Cashier',
                'customer' => $validated['customer'] ?? null,
                'items' => $validated['items'],
                'subtotal' => (float) $sale->subtotal,
                'discount_total' => (float) $sale->discount_amount,
                'tax_total' => (float) $sale->gst_amount,
                'round_off' => (float) $sale->round_off,
                'grand_total' => (float) $sale->grand_total,
                'payments' => $validated['payments'],
                'amount_paid' => (float) $sale->amount_tendered,
                'change_due' => (float) $sale->change_amount,
                'status' => 'completed',
            ]);
        });
    }

    /**
     * Get recent sales for history and reprints.
     */
    public function sales(): JsonResponse
    {
        $sales = PosSale::with(['items', 'customer'])
            ->latest('sale_date')
            ->limit(40)
            ->get();

        return response()->json($sales);
    }
}
