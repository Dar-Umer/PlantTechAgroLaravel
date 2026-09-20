<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 20)->default('material')->after('unit');
            $table->decimal('mrp', 12, 2)->nullable()->after('rate');
            $table->decimal('selling_price', 12, 2)->nullable()->after('mrp');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'mrp', 'selling_price']);
        });
    }
};
