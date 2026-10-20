<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos_sale_items')) {
            Schema::table('pos_sale_items', function (Blueprint $table) {
                if (! Schema::hasColumn('pos_sale_items', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->after('batch_id')->constrained('suppliers')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                if (! Schema::hasColumn('invoice_items', 'batch_id')) {
                    $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('product_batches')->nullOnDelete();
                }
                if (! Schema::hasColumn('invoice_items', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->after('batch_id')->constrained('suppliers')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_sale_items')) {
            Schema::table('pos_sale_items', function (Blueprint $table) {
                if (Schema::hasColumn('pos_sale_items', 'supplier_id')) {
                    $table->dropConstrainedForeignId('supplier_id');
                }
            });
        }

        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                if (Schema::hasColumn('invoice_items', 'supplier_id')) {
                    $table->dropConstrainedForeignId('supplier_id');
                }
                if (Schema::hasColumn('invoice_items', 'batch_id')) {
                    $table->dropConstrainedForeignId('batch_id');
                }
            });
        }
    }
};
