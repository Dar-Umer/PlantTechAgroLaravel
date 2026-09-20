<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductBatch::query()->with(['product', 'supplier'])->latest();

        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($supplierId = $request->query('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status = $request->query('status')) {
            abort_unless(in_array($status, array_keys(ProductBatch::STATUSES), true), 422, 'Invalid status filter.');
            $query->where('status', $status);
        }

        if ($expiry = $request->query('expiry')) {
            if (in_array((int) $expiry, [30, 60, 90], true)) {
                $query->expiringSoon((int) $expiry);
            } elseif ($expiry === 'expired') {
                $query->expired();
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        $batches = $query->paginate(25)->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'unit']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $totalBatches = ProductBatch::count();
        $activeBatches = ProductBatch::where('status', ProductBatch::STATUS_ACTIVE)->count();
        $expiringSoon = ProductBatch::expiringSoon(60)->count();
        $expiredCount = ProductBatch::expired()->count();

        return view('admin.product_batches.index', compact(
            'batches', 'products', 'suppliers',
            'totalBatches', 'activeBatches', 'expiringSoon', 'expiredCount'
        ));
    }

    public function export(Request $request)
    {
        $query = ProductBatch::query()->with(['product', 'supplier'])->latest();

        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $batches = $query->get();
        $filename = 'product-batches-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($batches) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Batch Number', 'Product', 'SKU', 'Status', 'Initial Qty',
                'Current Qty', 'Unit', 'Mfg Date', 'Expiry Date', 'Days to Expiry',
                'Unit Cost', 'Supplier', 'Created At',
            ]);

            foreach ($batches as $batch) {
                fputcsv($handle, array_map(
                    fn ($value) => self::csvCell($value),
                    [
                        $batch->batch_number,
                        $batch->product?->name,
                        $batch->product?->sku,
                        ProductBatch::STATUSES[$batch->status] ?? $batch->status,
                        $batch->initial_qty,
                        $batch->current_qty,
                        $batch->product?->unit,
                        $batch->mfg_date?->format('Y-m-d'),
                        $batch->expiry_date?->format('Y-m-d'),
                        $batch->daysUntilExpiry(),
                        $batch->unit_cost,
                        $batch->supplier?->name,
                        $batch->created_at?->format('Y-m-d H:i:s'),
                    ]
                ));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private static function csvCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[\s]*[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
