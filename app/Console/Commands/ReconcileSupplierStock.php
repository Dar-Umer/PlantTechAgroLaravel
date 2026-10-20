<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileSupplierStock extends Command
{
    protected $signature = 'inventory:reconcile-suppliers';

    protected $description = 'Reconcile supplier-specific batches and stock from all purchase bills.';

    public function handle(): int
    {
        $this->info('Starting supplier stock and batch reconciliation...');

        // 1. Find all purchase bill items from active bills where batch_id is null
        $unbatchedItems = PurchaseBillItem::query()
            ->whereNull('batch_id')
            ->whereHas('purchaseBill', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->with(['purchaseBill', 'product'])
            ->get();

        $this->info("Found {$unbatchedItems->count()} purchase bill line item(s) without batch records.");

        $createdBatches = 0;

        foreach ($unbatchedItems as $item) {
            $bill = $item->purchaseBill;
            $product = $item->product;

            if (! $bill || ! $product) {
                continue;
            }

            $supplierId = $bill->supplier_id;

            // Generate clean batch number and lot code
            $batchNumber = ! empty($item->batch_number) ? trim($item->batch_number) : ($bill->bill_number . '-LOT');
            $lotCode = 'LOT-' . ($bill->bill_date ? date('ymd', strtotime($bill->bill_date)) : date('ymd')) . '-' . strtoupper(substr(uniqid(), -4));

            DB::transaction(function () use ($item, $bill, $product, $supplierId, $batchNumber, $lotCode, &$createdBatches) {
                // Ensure supplier is attached to product
                if ($supplierId) {
                    $product->suppliers()->syncWithoutDetaching([$supplierId]);
                }

                // Check if an existing batch with this number already exists for this supplier
                $batch = ProductBatch::where('product_id', $product->id)
                    ->where('supplier_id', $supplierId)
                    ->where('batch_number', $batchNumber)
                    ->first();

                if (! $batch) {
                    $batch = ProductBatch::create([
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                        'lot_number' => $lotCode,
                        'inward_date' => $bill->bill_date ?? now()->toDateString(),
                        'mfg_date' => $item->mfg_date,
                        'expiry_date' => $item->expiry_date,
                        'initial_qty' => $item->quantity,
                        'current_qty' => $item->quantity,
                        'unit_cost' => $item->unit_cost ?? $product->rate ?? 0,
                        'selling_price' => $item->selling_price ?? $product->rate ?? 0,
                        'supplier_id' => $supplierId,
                        'status' => ProductBatch::STATUS_ACTIVE,
                        'notes' => 'Generated from Purchase Bill #' . $bill->bill_number,
                    ]);
                    $createdBatches++;
                } else {
                    $batch->current_qty = round((float) $batch->current_qty + (float) $item->quantity, 3);
                    $batch->initial_qty = round((float) $batch->initial_qty + (float) $item->quantity, 3);
                    $batch->refreshStatus();
                }

                $item->update([
                    'batch_id' => $batch->id,
                    'batch_number' => $batchNumber,
                ]);

                // Ensure corresponding StockMovement is tagged with batch and supplier
                StockMovement::where('product_id', $product->id)
                    ->where('purchase_bill_id', $bill->id)
                    ->update([
                        'batch_id' => $batch->id,
                        'supplier_id' => $supplierId,
                    ]);
            });
        }

        // 2. Adjust products where total batches exceed product stock_qty
        // (e.g. if a primary supplier batch was inflated before supplier-specific batches were backfilled)
        $allProducts = Product::with('batches')->get();
        foreach ($allProducts as $p) {
            $totalBatches = (float) $p->batches()->where('current_qty', '>', 0)->sum('current_qty');
            $stockQty = (float) $p->stock_qty;

            if ($totalBatches > $stockQty) {
                $excess = round($totalBatches - $stockQty, 3);
                $this->warn("Product '{$p->name}': Total batches ({$totalBatches}) exceed stock_qty ({$stockQty}) by {$excess}. Reconciling...");

                // Find auto-generated or primary batch that was inflated
                $inflatedBatch = $p->batches()
                    ->where('current_qty', '>=', $excess)
                    ->where('supplier_id', $p->supplier_id)
                    ->latest()
                    ->first();

                if (! $inflatedBatch) {
                    $inflatedBatch = $p->batches()
                        ->where('current_qty', '>=', $excess)
                        ->latest()
                        ->first();
                }

                if ($inflatedBatch) {
                    $inflatedBatch->current_qty = round((float) $inflatedBatch->current_qty - $excess, 3);
                    $inflatedBatch->refreshStatus();
                    $this->info("Reduced inflated batch '{$inflatedBatch->batch_number}' by {$excess}.");
                }
            }
        }

        $this->info("Supplier stock reconciliation completed! {$createdBatches} new supplier batch(es) created.");

        return self::SUCCESS;
    }
}
