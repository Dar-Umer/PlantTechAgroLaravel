<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('area')->nullable();
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            // No FK constraint: leads.converted_customer_id already references customers.
            $table->foreignId('lead_id')->nullable()->index();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
