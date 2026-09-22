<?php

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('product_batches', 'lot_number')) {
                $table->string('lot_number', 64)->nullable()->after('batch_number')->index();
            }
            if (! Schema::hasColumn('product_batches', 'inward_date')) {
                $table->date('inward_date')->nullable()->after('expiry_date');
            }

            // Add index on product_id first so the foreign key constraint remains satisfied
            // Check if foreign key index or index on product_id exists
            $table->index('product_id');
        });

        Schema::table('product_batches', function (Blueprint $table) {
            try {
                $table->dropUnique(['product_id', 'batch_number']);
            } catch (\Throwable $e) {
                // Ignore if unique index was already dropped
            }
        });

        // Backfill legacy products with positive stock that have no batches yet
        $products = Product::where('stock_qty', '>', 0)
            ->whereDoesntHave('batches')
            ->get();

        foreach ($products as $product) {
            ProductBatch::create([
                'product_id' => $product->id,
                'batch_number' => 'OPENING-LOT',
                'lot_number' => 'LOT-INIT-' . str_pad((string) $product->id, 4, '0', STR_PAD_LEFT),
                'initial_qty' => $product->stock_qty,
                'current_qty' => $product->stock_qty,
                'unit_cost' => $product->rate ?? 0,
                'supplier_id' => $product->supplier_id,
                'status' => ProductBatch::STATUS_ACTIVE,
                'inward_date' => now()->toDateString(),
                'notes' => 'Opening stock inventory batch backfill',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'batch_number']);
            $table->dropColumn(['lot_number', 'inward_date']);
        });
    }
};
