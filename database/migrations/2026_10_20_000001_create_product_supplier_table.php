<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_supplier')) {
            Schema::create('product_supplier', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'supplier_id']);
            });
        }

        // Backfill from products.supplier_id
        if (Schema::hasTable('products')) {
            $productsWithSupplier = DB::table('products')->whereNotNull('supplier_id')->get(['id', 'supplier_id']);
            foreach ($productsWithSupplier as $p) {
                DB::table('product_supplier')->insertOrIgnore([
                    'product_id' => $p->id,
                    'supplier_id' => $p->supplier_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Backfill from purchase_bills -> purchase_bill_items
        if (Schema::hasTable('purchase_bill_items') && Schema::hasTable('purchase_bills')) {
            $billItems = DB::table('purchase_bill_items')
                ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
                ->select('purchase_bill_items.product_id', 'purchase_bills.supplier_id')
                ->distinct()
                ->get();

            foreach ($billItems as $item) {
                DB::table('product_supplier')->insertOrIgnore([
                    'product_id' => $item->product_id,
                    'supplier_id' => $item->supplier_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Backfill from product_batches
        if (Schema::hasTable('product_batches')) {
            $batches = DB::table('product_batches')
                ->whereNotNull('supplier_id')
                ->select('product_id', 'supplier_id')
                ->distinct()
                ->get();

            foreach ($batches as $b) {
                DB::table('product_supplier')->insertOrIgnore([
                    'product_id' => $b->product_id,
                    'supplier_id' => $b->supplier_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
    }
};
