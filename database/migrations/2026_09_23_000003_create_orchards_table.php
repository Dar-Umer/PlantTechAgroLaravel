<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orchards', function (Blueprint $table) {
            $table->id();
            $table->string('orchard_id')->unique()->index();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->nullOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('area_kanals', 8, 2)->default(0);
            $table->unsignedInteger('tree_count')->default(0);
            $table->date('date_of_establishment')->nullable();
            $table->boolean('is_company_established')->default(false)->index();
            $table->string('variety_notes')->nullable();
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orchards');
    }
};
