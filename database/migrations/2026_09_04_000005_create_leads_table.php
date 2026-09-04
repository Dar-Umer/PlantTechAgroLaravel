<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->json('custom_fields')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('notes')->nullable();
            $table->string('source')->default('landing');
            $table->foreignId('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
