<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_customers', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            }
            if (! Schema::hasColumn('pos_customers', 'outstanding_balance')) {
                $table->decimal('outstanding_balance', 12, 2)->default(0)->after('total_spent');
            }
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_sales', 'amount_paid')) {
                $table->decimal('amount_paid', 12, 2)->default(0)->after('grand_total');
            }
            if (! Schema::hasColumn('pos_sales', 'balance_due')) {
                $table->decimal('balance_due', 12, 2)->default(0)->after('amount_paid');
            }
            if (! Schema::hasColumn('pos_sales', 'payment_status')) {
                $table->string('payment_status', 20)->default('paid')->after('balance_due')->index();
            }
        });

        if (! Schema::hasTable('pos_sale_payments')) {
            Schema::create('pos_sale_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_sale_id')->constrained('pos_sales')->cascadeOnDelete();
                $table->string('method', 30); // cash, upi, card, bank_transfer, other
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('reference')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sale_payments');

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance_due', 'payment_status']);
        });

        Schema::table('pos_customers', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'outstanding_balance']);
        });
    }
};
