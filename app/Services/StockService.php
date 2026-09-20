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
                }
            } elseif (! empty($batchNumber)) {
                $batchNumber = trim($batchNumber);
                $batch = ProductBatch::firstOrNew([
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                ]);

                if ($type === 'in') {
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
                    if ($unitCost) {
                        $batch->unit_cost = $unitCost;
                    }
                    $batch->refreshStatus();
                    $resolvedBatchId = $batch->id;
                } elseif ($type === 'out' && $batch->exists) {
                    if ((float) $batch->current_qty < $quantity) {
                        throw ValidationException::withMessages([
                            'batch_number' => "Insufficient stock in batch {$batch->batch_number}. Available: {$batch->current_qty} {$product->unit}.",
                        ]);
                    }
                    $batch->current_qty = round((float) $batch->current_qty - $quantity, 3);
                    $batch->refreshStatus();
                    $resolvedBatchId = $batch->id;
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
}
