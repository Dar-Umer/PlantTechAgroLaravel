<?php

namespace App\Services;

use App\Mail\SupplierLowStockMail;
use App\Models\Admin;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\ProductBatch;
use App\Support\Format;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Record a stock movement and update the product's stock level.
     *
     * @param  'in'|'out'|'adjustment'  $type
     */
    public static function record(
        Product $product,
        string $type,
        float $quantity,
        ?string $reference = null,
        ?string $note = null,
        ?int $supplierId = null,
        ?float $unitCost = null,
        ?int $userId = null,
        ?int $batchId = null,
        ?string $batchNumber = null,
        ?string $mfgDate = null,
        ?string $expiryDate = null,
    ): StockMovement {
        if (! in_array($type, ['in', 'out', 'adjustment'], true)) {
            throw new \InvalidArgumentException("Invalid stock movement type [{$type}].");
        }

        $quantity = round($quantity, 3);

        if ($quantity <= 0 && $type !== 'adjustment') {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $type, $quantity, $reference, $note, $supplierId, $unitCost, $userId, $batchId, $batchNumber, $mfgDate, $expiryDate) {
            $wasLow = $product->isLowStock();

            $movementQty = match ($type) {
                'in' => $quantity,
                'out' => -$quantity,
                'adjustment' => $quantity - (float) $product->stock_qty,
            };

            $newStock = match ($type) {
                'in' => (float) $product->stock_qty + $quantity,
                'out' => (float) $product->stock_qty - $quantity,
                'adjustment' => $quantity,
            };

            $newStock = round($newStock, 3);

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock for {$product->name}. Available: " . Format::qty($product->stock_qty) . " {$product->unit}.",
                ]);
            }

            $resolvedBatchId = null;
            if ($batchId) {
                $batch = ProductBatch::where('product_id', $product->id)->find($batchId);
                if ($batch) {
                    if ($type === 'in') {
                        $batch->initial_qty = round((float) $batch->initial_qty + $quantity, 3);
                        $batch->current_qty = round((float) $batch->current_qty + $quantity, 3);
                        $batch->refreshStatus();
                    } elseif ($type === 'out') {
                        if ((float) $batch->current_qty < $quantity) {
                            throw ValidationException::withMessages([
                                'batch_id' => "Insufficient stock in batch {$batch->batch_number}. Available: {$batch->current_qty} {$product->unit}.",
                            ]);
                        }
                        $batch->current_qty = round((float) $batch->current_qty - $quantity, 3);
                        $batch->refreshStatus();
                    }
                    $resolvedBatchId = $batch->id;
                    if ($unitCost === null && $batch->unit_cost !== null) {
                        $unitCost = (float) $batch->unit_cost;
                    }
                }
            } elseif (! empty($batchNumber)) {
                $batchNumber = trim($batchNumber);

                if ($type === 'in') {
                    $existingBatch = ProductBatch::where('product_id', $product->id)
                        ->where('batch_number', $batchNumber)
                        ->first();

                    // If it exists with a DIFFERENT unit cost, create a separate lot so older batch rate is preserved!
                    if ($existingBatch && $unitCost !== null && $existingBatch->unit_cost !== null && (float) $existingBatch->unit_cost !== (float) $unitCost) {
                        $count = ProductBatch::where('product_id', $product->id)->where('batch_number', 'like', $batchNumber.'%')->count();
                        $batchNumber = $batchNumber . ' (Lot ' . ($count + 1) . ')';
                        $batch = new ProductBatch([
                            'product_id' => $product->id,
                            'batch_number' => $batchNumber,
                            'lot_number' => 'LOT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                        ]);
                    } else {
                        $batch = $existingBatch ?? new ProductBatch([
                            'product_id' => $product->id,
                            'batch_number' => $batchNumber,
                            'lot_number' => 'LOT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                        ]);
                    }

                    $batch->initial_qty = round(((float) $batch->initial_qty) + $quantity, 3);
                    $batch->current_qty = round(((float) $batch->current_qty) + $quantity, 3);
                    if ($mfgDate) {
                        $batch->mfg_date = $mfgDate;
                    }
                    if ($expiryDate) {
                        $batch->expiry_date = $expiryDate;
                    }
                    if ($supplierId) {
                        $batch->supplier_id = $supplierId;
                    }
                    if ($unitCost !== null) {
                        $batch->unit_cost = $unitCost;
                    }
                    $batch->inward_date = $batch->inward_date ?? now()->toDateString();
                    $batch->refreshStatus();
                    $resolvedBatchId = $batch->id;
                } elseif ($type === 'out') {
                    $batch = ProductBatch::where('product_id', $product->id)->where('batch_number', $batchNumber)->first();
                    if ($batch && $batch->exists) {
                        if ((float) $batch->current_qty < $quantity) {
                            throw ValidationException::withMessages([
                                'batch_number' => "Insufficient stock in batch {$batch->batch_number}. Available: {$batch->current_qty} {$product->unit}.",
                            ]);
                        }
                        $batch->current_qty = round((float) $batch->current_qty - $quantity, 3);
                        $batch->refreshStatus();
                        $resolvedBatchId = $batch->id;
                        if ($unitCost === null && $batch->unit_cost !== null) {
                            $unitCost = (float) $batch->unit_cost;
                        }
                    }
                }
            } else {
                // Neither batch_id nor batch_number explicitly specified
                if ($type === 'in') {
                    // Auto-generate lot so every inward preserves its specific rate
                    $lotCode = 'LOT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
                    $batch = ProductBatch::create([
                        'product_id' => $product->id,
                        'batch_number' => $lotCode,
                        'lot_number' => $lotCode,
                        'inward_date' => now()->toDateString(),
                        'mfg_date' => $mfgDate,
                        'expiry_date' => $expiryDate,
                        'initial_qty' => $quantity,
                        'current_qty' => $quantity,
                        'unit_cost' => $unitCost ?? $product->rate,
                        'supplier_id' => $supplierId ?? $product->supplier_id,
                        'status' => ProductBatch::STATUS_ACTIVE,
                        'notes' => $note,
                    ]);
                    $resolvedBatchId = $batch->id;
                } elseif ($type === 'out') {
                    // FIFO: Deduct from oldest active batches
                    $activeBatches = ProductBatch::where('product_id', $product->id)
                        ->where('current_qty', '>', 0)
                        ->orderByRaw('COALESCE(inward_date, created_at) ASC, id ASC')
                        ->get();

                    $remainingToDeduct = $quantity;
                    $deductedLots = [];

                    foreach ($activeBatches as $actBatch) {
                        if ($remainingToDeduct <= 0) {
                            break;
                        }

                        $take = min((float) $actBatch->current_qty, $remainingToDeduct);
                        $actBatch->current_qty = round((float) $actBatch->current_qty - $take, 3);
                        $actBatch->refreshStatus();

                        if ($resolvedBatchId === null) {
                            $resolvedBatchId = $actBatch->id;
                            if ($unitCost === null && $actBatch->unit_cost !== null) {
                                $unitCost = (float) $actBatch->unit_cost;
                            }
                        }

                        $deductedLots[] = "{$actBatch->batch_number} (-{$take})";
                        $remainingToDeduct = round($remainingToDeduct - $take, 3);
                    }

                    if (! empty($deductedLots) && $note === null) {
                        $note = 'FIFO: ' . implode(', ', $deductedLots);
                    }
                }
            }

            $product->forceFill(['stock_qty' => $newStock])->save();

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'batch_id' => $resolvedBatchId,
                'type' => $type,
                'quantity' => $movementQty,
                'stock_after' => $newStock,
                'unit_cost' => $unitCost,
                'supplier_id' => $supplierId,
                'reference' => $reference,
                'note' => $note,
                'created_by' => $userId,
            ]);

            if ($movement->reference === null || trim($movement->reference) === '') {
                $movement->forceFill([
                    'reference' => StockMovement::MANUAL_REF_PREFIX . str_pad((string) $movement->id, 4, '0', STR_PAD_LEFT),
                ])->save();
            }

            $isLow = $product->isLowStock();

            if (! $wasLow && $isLow) {
                static::dispatchLowStockAlerts($product->refresh());
            }

            return $movement;
        });
    }

    public static function dispatchLowStockAlerts(Product $product): void
    {
        $supplier = $product->supplier;

        foreach (Admin::where('is_active', true)->get() as $admin) {
            $admin->notify(new LowStockAlert($product));
        }

        if ($supplier?->email) {
            Mail::to($supplier->email)->send(new SupplierLowStockMail($product, $supplier));
        }
    }

    /**
     * Preview FIFO allocation for a given product and quantity without mutating state.
     * Returns an array of batches with their consumed quantities and unit costs.
     *
     * @return array<int, array{batch_id: ?int, batch_number: string, unit_cost: float, quantity: float}>
     */
    public static function allocateFifo(Product $product, float $quantity): array
    {
        $activeBatches = ProductBatch::where('product_id', $product->id)
            ->where('current_qty', '>', 0)
            ->orderByRaw('COALESCE(inward_date, created_at) ASC, id ASC')
            ->get();

        $allocations = [];
        $remaining = round($quantity, 3);

        foreach ($activeBatches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min((float) $batch->current_qty, $remaining);
            $allocations[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'unit_cost' => (float) ($batch->unit_cost ?? $product->rate ?? 0),
                'quantity' => $take,
            ];
            $remaining = round($remaining - $take, 3);
        }

        if ($remaining > 0) {
            $allocations[] = [
                'batch_id' => null,
                'batch_number' => 'UNTRACKED',
                'unit_cost' => (float) ($product->rate ?? 0),
                'quantity' => $remaining,
            ];
        }

        return $allocations;
    }
}
