<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('supplier')->orderBy('name');

        if ($request->query('stock') === 'low') {
            $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                ->where('low_stock_threshold', '>', 0);
        }

        if ($supplierId = $request->query('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($search = trim((string) $request->query('q'))) {
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
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'units' => $this->units(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $openingStock = (float) ($data['stock_qty'] ?? 0);
        unset($data['stock_qty']);

        $product = Product::create($data);

        if ($openingStock > 0) {
            StockService::record($product, 'in', $openingStock, 'OPENING', 'Opening stock', null, null, $request->user('admin')->id ?? null);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'units' => $this->units(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
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
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'rate' => ['required', 'numeric', 'min:0'],
            'gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock_qty' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')],
            'is_active' => ['nullable', 'boolean'],
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
