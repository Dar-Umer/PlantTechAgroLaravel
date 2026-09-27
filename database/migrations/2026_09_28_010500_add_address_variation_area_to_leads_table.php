<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
            $table->string('service_variation')->nullable()->after('service_id');
            $table->decimal('area', 10, 2)->nullable()->after('service_variation');
            $table->string('unit', 50)->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['address', 'service_variation', 'area', 'unit']);
        });
    }
};
