<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::query()->with(['product', 'supplier', 'createdBy'])->latest();

        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $movements = $query->paginate(25)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name', 'unit']);

        return view('admin.stock_movements.index', compact('movements', 'products'));
    }

    public function create(Request $request)
    {
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'unit', 'stock_qty', 'sku']);
        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);
        $selectedProduct = $request->query('product_id');

        return view('admin.stock_movements.create', compact('products', 'suppliers', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'quantity_final' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'adjustment') {
            if (! array_key_exists('quantity_final', $data) || $data['quantity_final'] === null) {
                return back()->withInput()->withErrors(['quantity_final' => 'Please enter the actual counted stock level.']);
            }

            if ($data['note'] === null) {
                return back()->withInput()->withErrors(['note' => 'A note is required for stock adjustments.']);
            }

            $data['quantity'] = $data['quantity_final'];
        } elseif ($data['quantity'] === null || (float) $data['quantity'] <= 0) {
            return back()->withInput()->withErrors(['quantity' => 'Quantity must be greater than zero.']);
        }

        StockService::record(
            $product,
            $data['type'],
            (float) $data['quantity'],
            null,
            $data['note'],
            $data['type'] === 'in' ? $data['supplier_id'] : null,
            $data['type'] === 'in' ? $data['unit_cost'] : null,
            $request->user('admin')->id ?? null,
        );

        return redirect()->route('admin.stock-movements.index')->with('success', 'Stock updated. '.$product->name.' is now at '.$product->refresh()->stock_qty.' '.$product->unit.'.');
    }
}
