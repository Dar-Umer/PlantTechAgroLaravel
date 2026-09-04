<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_stage_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['service_stage_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_stage_products');
    }
};
