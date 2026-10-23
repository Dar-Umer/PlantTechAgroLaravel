<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->decimal('labour_charges', 12, 2)->default(0)->after('subtotal');
            $table->decimal('labour_cost', 12, 2)->default(0)->after('labour_charges');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn(['labour_charges', 'labour_cost']);
        });
    }
};
