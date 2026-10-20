<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseBillItem;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditInventory extends Command
{
    protected $signature = 'inventory:audit
                            {--product= : Filter by Product ID, SKU, or Name}
                            {--fix : Automatically repair detected discrepancies}';

    protected $description = 'Audit inventory stock against purchase bills, inward movements, batches, and sales.';

    public function handle(): int
    {
        $this->info('Starting inventory audit...');

        $query = Product::query()->with(['supplier', 'suppliers', 'batches']);

        if ($filter = $this->option('product')) {
            $query->where(function ($q) use ($filter) {
                if (is_numeric($filter)) {
                    $q->where('id', (int) $filter);
                } else {
                    $q->where('sku', 'like', "%{$filter}%")
                      ->orWhere('name', 'like', "%{$filter}%");
                }
            });
        }

        $products = $query->orderBy('name')->get();

        if ($products->isEmpty()) {
            $this->warn('No products found matching criteria.');
            return self::SUCCESS;
        }

        $rows = [];
        $allIssues = [];
        $discrepancyCount = 0;
        $shouldFix = (bool) $this->option('fix');

        foreach ($products as $p) {
            // 1. Total purchased via approved Purchase Bills
            $purchasedQty = (float) PurchaseBillItem::where('product_id', $p->id)
                ->whereHas('purchaseBill', fn($b) => $b->where('status', '!=', 'cancelled'))
                ->sum('quantity');

            // 2. Opening stock from movements
            $openingQty = (float) StockMovement::where('product_id', $p->id)
                ->where('reference', 'OPENING')
                ->where('type', 'in')
                ->sum('quantity');

            // 3. Total Inward Movements
            $totalInward = (float) StockMovement::where('product_id', $p->id)
                ->where('type', 'in')
                ->sum('quantity');

            // 4. Total Outward Movements (Sales + manual out)
            $totalOutward = abs((float) StockMovement::where('product_id', $p->id)
                ->where('type', 'out')
                ->sum('quantity'));

            $netMovementStock = round($totalInward - $totalOutward, 3);
            $currentStock = round((float) $p->stock_qty, 3);

            // 5. Active batches stock
            $activeBatchQty = round((float) $p->batches()->where('current_qty', '>', 0)->sum('current_qty'), 3);
            $hasBatches = $p->batches()->exists();

            // Check for discrepancies
            $issues = [];

            // A. Stock qty does not match net movements (only if movements exist)
            if ($totalInward > 0 && abs($currentStock - $netMovementStock) > 0.001) {
                $diff = round($currentStock - $netMovementStock, 3);
                $issues[] = "Stock qty ({$currentStock}) differs from Net Movements ({$netMovementStock}) by {$diff}";
            }

            // B. Purchased bills vs Inwards
            $billInwards = (float) StockMovement::where('product_id', $p->id)
                ->whereNotNull('purchase_bill_id')
                ->where('type', 'in')
                ->sum('quantity');

            if (abs($purchasedQty - $billInwards) > 0.001) {
                $issues[] = "Purchased on bills ({$purchasedQty}) != Bill Inward Movements ({$billInwards})";
            }

            // C. Batches vs Stock
            if ($hasBatches && $activeBatchQty > 0 && abs($currentStock - $activeBatchQty) > 0.001) {
                $issues[] = "Active Batches total ({$activeBatchQty}) != Product stock_qty ({$currentStock})";
            }

            // D. Missing supplier_id on opening movement
            $unattributedOpening = StockMovement::where('product_id', $p->id)
                ->where('reference', 'OPENING')
                ->whereNull('supplier_id')
                ->count();

            if ($unattributedOpening > 0 && $p->supplier_id) {
                $issues[] = "{$unattributedOpening} Opening movement(s) missing supplier_id";
            }

            $status = empty($issues) ? '<fg=green>MATCH</>' : '<fg=red>MISMATCH</>';
            if (!empty($issues)) {
                $discrepancyCount++;
                $allIssues[$p->name] = $issues;
            }

            $rows[] = [
                $p->id,
                substr($p->name, 0, 25),
                $currentStock . ' ' . $p->unit,
                $purchasedQty . ' ' . $p->unit,
                $openingQty . ' ' . $p->unit,
                $totalOutward . ' ' . $p->unit,
                $netMovementStock . ' ' . $p->unit,
                $hasBatches ? $activeBatchQty : 'N/A',
                $status,
            ];

            // Apply fixes if --fix flag was passed
            if ($shouldFix && !empty($issues)) {
                DB::transaction(function () use ($p, $netMovementStock, $currentStock, $unattributedOpening, $totalInward, $hasBatches) {
                    // 1. Backfill supplier_id on OPENING movement
                    if ($unattributedOpening > 0 && $p->supplier_id) {
                        StockMovement::where('product_id', $p->id)
                            ->where('reference', 'OPENING')
                            ->whereNull('supplier_id')
                            ->update(['supplier_id' => $p->supplier_id]);
                    }

                    // 2. If movements exist and stock_qty has drifted, reconcile stock_qty
                    if ($totalInward > 0 && abs($currentStock - $netMovementStock) > 0.001) {
                        $p->update(['stock_qty' => max(0, $netMovementStock)]);
                        $currentStock = max(0, $netMovementStock);
                    }

                    // 3. Backfill missing batches for Purchase Bill items
                    $unbatchedItems = PurchaseBillItem::where('product_id', $p->id)
                        ->whereNull('batch_id')
                        ->whereHas('purchaseBill', fn($b) => $b->where('status', '!=', 'cancelled'))
                        ->with('purchaseBill')
                        ->get();

                    foreach ($unbatchedItems as $item) {
                        $bill = $item->purchaseBill;
                        $batchNumber = !empty($item->batch_number) ? trim($item->batch_number) : ('BILL-' . ($bill ? $bill->bill_number : 'INIT'));
                        $lotCode = 'LOT-' . ($bill?->bill_date ? date('ymd', strtotime($bill->bill_date)) : date('ymd')) . '-' . strtoupper(substr(uniqid(), -4));

                        $batch = ProductBatch::create([
                            'product_id' => $p->id,
                            'batch_number' => $batchNumber,
                            'lot_number' => $lotCode,
                            'inward_date' => $bill?->bill_date ?? now()->toDateString(),
                            'mfg_date' => $item->mfg_date,
                            'expiry_date' => $item->expiry_date,
                            'initial_qty' => $item->quantity,
                            'current_qty' => $item->quantity,
                            'unit_cost' => $item->unit_cost ?? $p->rate ?? 0,
                            'selling_price' => $item->selling_price ?? $p->rate ?? 0,
                            'supplier_id' => $bill?->supplier_id ?? $p->supplier_id,
                            'status' => ProductBatch::STATUS_ACTIVE,
                            'notes' => 'Auto-created from Purchase Bill #' . ($bill ? $bill->bill_number : 'UNBATCHED'),
                        ]);

                        $item->update(['batch_id' => $batch->id, 'batch_number' => $batchNumber]);

                        StockMovement::where('product_id', $p->id)
                            ->where('purchase_bill_id', $bill?->id)
                            ->whereNull('batch_id')
                            ->update(['batch_id' => $batch->id]);
                    }

                    // 4. Synchronize batches with product current stock
                    if ($hasBatches || $unbatchedItems->isNotEmpty()) {
                        $freshBatchQty = round((float) $p->batches()->where('current_qty', '>', 0)->sum('current_qty'), 3);
                        $batchDiff = round($currentStock - $freshBatchQty, 3);

                        if ($batchDiff > 0.001) {
                            // Unbatched stock exists: create a dedicated lot for the difference
                            ProductBatch::create([
                                'product_id' => $p->id,
                                'batch_number' => 'LOT-UNBATCHED',
                                'lot_number' => 'LOT-' . date('ymd') . '-INIT',
                                'inward_date' => $p->created_at?->toDateString() ?? now()->toDateString(),
                                'initial_qty' => $batchDiff,
                                'current_qty' => $batchDiff,
                                'unit_cost' => (float) ($p->rate ?? 0),
                                'selling_price' => (float) ($p->selling_price ?? $p->rate ?? 0),
                                'supplier_id' => $p->supplier_id,
                                'status' => ProductBatch::STATUS_ACTIVE,
                                'notes' => 'Auto-generated lot for unbatched physical stock',
                            ]);
                        } elseif ($batchDiff < -0.001) {
                            // Batches exceed physical stock: deduct the difference via FIFO
                            $toDeduct = abs($batchDiff);
                            $batchesToDeduct = $p->batches()->where('current_qty', '>', 0)->orderBy('created_at')->get();
                            foreach ($batchesToDeduct as $b) {
                                if ($toDeduct <= 0) break;
                                $take = min((float) $b->current_qty, $toDeduct);
                                $b->current_qty = round((float) $b->current_qty - $take, 3);
                                $b->refreshStatus();
                                $toDeduct -= $take;
                            }
                        }
                    }
                });
            }
        }

        $this->table(
            ['ID', 'Product', 'Current Stock', 'Purchased Bills', 'Opening', 'Sold/Out', 'Net Mvmt', 'Batches', 'Status'],
            $rows
        );

        if ($discrepancyCount === 0) {
            $this->info("All {$products->count()} products match perfectly! No discrepancies found.");
        } else {
            $this->newLine();
            $this->warn("Found {$discrepancyCount} product(s) with notes/discrepancies:");
            foreach ($allIssues as $prodName => $notes) {
                $this->line("<fg=yellow;options=bold>• {$prodName}:</>");
                foreach ($notes as $note) {
                    $this->line("    - {$note}");
                }
            }
            $this->newLine();
            if ($shouldFix) {
                $this->info("Repairs applied where possible! Run 'php artisan inventory:audit' again to verify.");
            } else {
                $this->line("Tip: Run <fg=yellow>php artisan inventory:audit --fix</> to automatically reconcile stock movements and backfill supplier assignments.");
            }
        }

        return self::SUCCESS;
    }
}
