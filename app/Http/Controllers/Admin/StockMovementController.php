<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\StockService;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->applyFilters($request, StockMovement::query()->with(['product', 'supplier', 'createdBy'])->latest());

        $movements = $query->paginate(25)->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'unit']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $monthStart = now()->startOfMonth();

        $inMonthQty = (float) StockMovement::where('type', 'in')->where('created_at', '>=', $monthStart)->sum('quantity');
        $outMonthQty = (float) StockMovement::where('type', 'out')->where('created_at', '>=', $monthStart)->sum(DB::raw('ABS(quantity)'));
        $adjustmentCount = StockMovement::where('type', 'adjustment')->where('created_at', '>=', $monthStart)->count();
        $netMonthQty = (float) StockMovement::where('created_at', '>=', $monthStart)->sum('quantity');
        $stockValue = (float) Product::sum(DB::raw('COALESCE(stock_qty, 0) * COALESCE(rate, 0)'));

        return view('admin.stock_movements.index', compact(
            'movements', 'products', 'suppliers',
            'inMonthQty', 'outMonthQty', 'adjustmentCount', 'netMonthQty', 'stockValue',
        ));
    }

    public function show(StockMovement $movement)
    {
        $movement->load(['product', 'supplier', 'createdBy']);

        return view('admin.stock_movements.show', compact('movement'));
    }

    public function create(Request $request)
    {
        $products = Product::active()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'rate', 'stock_qty', 'low_stock_threshold']);
        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);
        $preselectId = $request->query('product_id');

        return view('admin.stock_movements.create', compact('products', 'suppliers', 'preselectId'));
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

        return redirect()->route('admin.stock-movements.index')->with('success', 'Stock updated. '.$product->name.' is now at '.Format::qty($product->refresh()->stock_qty).' '.$product->unit.'.');
    }

    public function export(Request $request)
    {
        $query = $this->applyFilters($request, StockMovement::query()->with(['product', 'supplier', 'createdBy'])->latest());

        $movements = $query->get();

        $filename = 'stock-movements-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Reference', 'Date', 'Product', 'SKU', 'Type', 'Quantity',
                'Before', 'After', 'Unit Cost', 'Value', 'Supplier', 'Note', 'Created By',
            ]);

            foreach ($movements as $movement) {
                fputcsv($handle, array_map(
                    fn ($value) => self::csvCell($value),
                    [
                        $movement->reference,
                        $movement->created_at?->format('Y-m-d H:i:s'),
                        $movement->product?->name,
                        $movement->product?->sku,
                        StockMovement::TYPES[$movement->type] ?? $movement->type,
                        $movement->quantity,
                        $movement->stockBefore(),
                        $movement->stock_after,
                        $movement->unit_cost,
                        $movement->movementValue(),
                        $movement->supplier?->name,
                        $movement->note,
                        $movement->createdBy?->name,
                    ]
                ));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Neutralize CSV formula injection: cells starting with = + - @ (or
     * tab/CR after them) execute as formulas when opened in Excel.
     */
    private static function csvCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[\s]*[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    private function applyFilters(Request $request, $query)
    {
        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($supplierId = $request->query('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}