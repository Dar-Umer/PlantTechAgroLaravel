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
                DB::transaction(function () use ($p, $netMovementStock, $currentStock, $unattributedOpening, $totalInward, $hasBatches, $activeBatchQty) {
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

                    // 3. If batches exist and differ from product stock, synchronize
                    if ($hasBatches && abs($currentStock - $activeBatchQty) > 0.001) {
                        $diff = round($currentStock - $activeBatchQty, 3);
                        $targetBatch = $p->batches()->where('supplier_id', $p->supplier_id)->where('current_qty', '>', 0)->latest()->first()
                            ?? $p->batches()->where('current_qty', '>', 0)->latest()->first();

                        if ($targetBatch && ((float) $targetBatch->current_qty + $diff) >= 0) {
                            $targetBatch->current_qty = round((float) $targetBatch->current_qty + $diff, 3);
                            $targetBatch->refreshStatus();
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
