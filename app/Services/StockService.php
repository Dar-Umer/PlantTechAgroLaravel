<?php

namespace App\Services;

use App\Mail\SupplierLowStockMail;
use App\Models\Admin;
use App\Models\Product;
use App\Models\StockMovement;
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

        return DB::transaction(function () use ($product, $type, $quantity, $reference, $note, $supplierId, $unitCost, $userId) {
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
                    'quantity' => "Insufficient stock for {$product->name}. Available: {$product->stock_qty} {$product->unit}.",
                ]);
            }

            $product->forceFill(['stock_qty' => $newStock])->save();

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $movementQty,
                'stock_after' => $newStock,
                'unit_cost' => $unitCost,
                'supplier_id' => $supplierId,
                'reference' => $reference,
                'note' => $note,
                'created_by' => $userId,
            ]);

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
