<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 64)->unique()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('supplier_invoice_no', 100)->nullable()->index();
            $table->date('bill_date')->index();
            $table->date('due_date')->nullable();
            
            $table->string('status', 20)->default('received')->index(); // 'received', 'draft', 'cancelled'
            $table->string('payment_status', 20)->default('unpaid')->index(); // 'unpaid', 'partial', 'paid'
            
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('balance_due', 14, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->string('attachment', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['supplier_id', 'bill_date']);
            $table->index(['payment_status', 'due_date']);
        });

        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            
            $table->string('batch_number', 100)->nullable();
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 64)->unique()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_bill_id')->nullable()->constrained('purchase_bills')->nullOnDelete();
            
            $table->decimal('amount', 14, 2);
            $table->date('payment_date')->index();
            $table->string('payment_method', 30)->default('bank_transfer'); // 'cash', 'bank_transfer', 'cheque', 'upi', 'neft_rtgs', 'other'
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['supplier_id', 'payment_date']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('purchase_bill_id')->nullable()->after('supplier_id')->constrained('purchase_bills')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['purchase_bill_id']);
            $table->dropColumn('purchase_bill_id');
        });

        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('purchase_bill_items');
        Schema::dropIfExists('purchase_bills');
    }
};
