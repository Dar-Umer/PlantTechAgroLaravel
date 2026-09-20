<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number', 64);
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->decimal('initial_qty', 12, 3)->default(0);
            $table->decimal('current_qty', 12, 3)->default(0);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
