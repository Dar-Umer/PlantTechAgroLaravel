<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable()->after('completed_at');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable()->after('converted_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sent_at');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sent_at');
        });
    }
};