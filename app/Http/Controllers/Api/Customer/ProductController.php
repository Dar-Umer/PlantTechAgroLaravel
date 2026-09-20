<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Media;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Sellable product catalog for the customer app.
     * Service materials are excluded; only active sellables listed.
     */
    public function index(Request $request)
    {
        $query = Product::query()->active()->sellable()->orderBy('name');

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $paginated = $query->paginate(15)->withQueryString();

        return response()->json([
            'products' => $paginated->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'hsn_code' => $product->hsn_code,
                'description' => $product->description,
                'image' => Media::url($product->image),
                'unit' => $product->unit,
                'mrp' => $product->mrp !== null ? (float) $product->mrp : null,
                'price' => $product->salePrice(),
                'gst_rate' => (float) $product->gst_rate,
                'in_stock' => (float) $product->stock_qty > 0,
                'stock_qty' => (float) $product->stock_qty,
            ])->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}
