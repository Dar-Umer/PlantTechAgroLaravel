<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('product_batches')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn_code', 16)->nullable()->after('sku');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('hsn_code', 16)->nullable()->after('name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('gstin', 20)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('hsn_code');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('hsn_code');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('gstin');
        });
    }
};
