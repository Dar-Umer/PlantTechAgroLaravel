<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('product_batches', 'selling_price')) {
                $table->decimal('selling_price', 12, 2)->nullable()->after('unit_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (Schema::hasColumn('product_batches', 'selling_price')) {
                $table->dropColumn('selling_price');
            }
        });
    }
};
