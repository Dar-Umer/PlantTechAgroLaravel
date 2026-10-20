<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['supplier', 'suppliers'])->orderBy('name');

        if ($request->query('stock') === 'low') {
            $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                ->where('low_stock_threshold', '>', 0);
        }

        if ($supplierId = $request->query('supplier_id')) {
            $query->where(function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId)
                  ->orWhereHas('suppliers', fn($s) => $s->where('suppliers.id', $supplierId))
                  ->orWhereHas('batches', fn($b) => $b->where('supplier_id', $supplierId))
                  ->orWhereHas('movements', fn($m) => $m->where('supplier_id', $supplierId))
                  ->orWhereExists(function ($sub) use ($supplierId) {
                      $sub->select(DB::raw(1))
                          ->from('purchase_bill_items')
                          ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
                          ->whereColumn('purchase_bill_items.product_id', 'products.id')
                          ->where('purchase_bills.supplier_id', $supplierId);
                  });
            });
        }

        if ($type = $request->query('type')) {
            abort_unless(in_array($type, array_keys(Product::TYPES), true), 422, 'Invalid type filter.');
            $query->where('type', $type);
        }

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $lowStockCount = Product::whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)->count();

        return view('admin.products.index', compact('products', 'suppliers', 'lowStockCount'));
    }

    public function create()
    {
        return view('admin.products.create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name', 'phone']),
            'units' => $this->units(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $supplierIds = $request->input('supplier_ids', []);
        if (! is_array($supplierIds)) {
            $supplierIds = array_filter([(int) $supplierIds]);
        } else {
            $supplierIds = array_values(array_filter(array_map('intval', $supplierIds)));
        }

        if (! empty($data['supplier_id']) && ! in_array((int) $data['supplier_id'], $supplierIds, true)) {
            $supplierIds[] = (int) $data['supplier_id'];
        }

        if (empty($data['supplier_id']) && ! empty($supplierIds)) {
            $data['supplier_id'] = $supplierIds[0];
        }

        if ($request->hasFile('image')) {
            $data['image'] = Media::storeImage($request->file('image'), 'products');
        }

        $openingStock = (float) ($data['stock_qty'] ?? 0);
        unset($data['stock_qty']);
        $data['type'] = $data['type'] ?? 'material';
        $data['low_stock_threshold'] = (float) ($data['low_stock_threshold'] ?? 0);
        $data['gst_rate'] = (float) ($data['gst_rate'] ?? 0);

        $product = Product::create($data);

        if (! empty($supplierIds)) {
            $product->suppliers()->sync($supplierIds);
        }

        if ($openingStock > 0) {
            StockService::record($product, 'in', $openingStock, 'OPENING', 'Opening stock', $product->supplier_id, null, $request->user('admin')->id ?? null);
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => "Product '{$product->name}' created successfully.",
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'rate' => (float) $product->rate,
                    'cost_price' => (float) $product->rate,
                    'selling_price' => $product->selling_price !== null ? (float) $product->selling_price : (float) $product->rate,
                    'stock_qty' => (float) $product->stock_qty,
                    'gst_rate' => $product->gst_rate !== null ? (float) $product->gst_rate : 0,
                    'type' => $product->type,
                ],
            ], 201);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load('suppliers');

        return view('admin.products.edit', [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name', 'phone']),
            'selectedSupplierIds' => $product->suppliers->pluck('id')->all(),
            'units' => $this->units(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);

        $supplierIds = $request->input('supplier_ids', []);
        if (! is_array($supplierIds)) {
            $supplierIds = array_filter([(int) $supplierIds]);
        } else {
            $supplierIds = array_values(array_filter(array_map('intval', $supplierIds)));
        }

        if (! empty($data['supplier_id']) && ! in_array((int) $data['supplier_id'], $supplierIds, true)) {
            $supplierIds[] = (int) $data['supplier_id'];
        }

        if (empty($data['supplier_id']) && ! empty($supplierIds)) {
            $data['supplier_id'] = $supplierIds[0];
        }

        $product->suppliers()->sync($supplierIds);

        if ($request->hasFile('image')) {
            $data['image'] = Media::storeImage($request->file('image'), 'products');
        } else {
            unset($data['image']);
        }

        unset($data['stock_qty']);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->movements()->exists()) {
            return back()->with('error', 'This product has stock movements and cannot be deleted. Deactivate it instead.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function notifySupplier(Request $request, Product $product)
    {
        if (! $product->supplier?->email) {
            return back()->with('error', 'This product has no supplier with an email address assigned.');
        }

        StockService::dispatchLowStockAlerts($product);

        return back()->with('success', 'Low stock alert email sent to '.$product->supplier->email.'.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $skuRule = ['nullable', 'string', 'max:64'];
        if ($ignoreId) {
            $skuRule[] = Rule::unique('products', 'sku')->ignore($ignoreId);
        } else {
            $skuRule[] = Rule::unique('products', 'sku');
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => $skuRule,
            'hsn_code' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'type' => ['nullable', Rule::in(array_keys(Product::TYPES))],
            'rate' => ['required', 'numeric', 'min:0'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock_qty' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
        ]);
    }

    private function units(): array
    {
        return [
            'pcs' => 'Pieces',
            'kg' => 'Kilogram',
            'g' => 'Gram',
            'ltr' => 'Litre',
            'ml' => 'Millilitre',
            'mtr' => 'Metre',
            'bag' => 'Bag',
            'box' => 'Box',
            'set' => 'Set',
            'roll' => 'Roll',
        ];
    }
}
