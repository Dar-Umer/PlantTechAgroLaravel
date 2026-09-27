<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_stages', function (Blueprint $table) {
            $table->boolean('notify_customer')->default(true)->after('requires_pdf');
            $table->string('notification_title')->nullable()->after('notify_customer');
            $table->text('notification_body')->nullable()->after('notification_title');
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->boolean('notify_customer')->default(true)->after('requires_pdf');
            $table->string('notification_title')->nullable()->after('notify_customer');
            $table->text('notification_body')->nullable()->after('notification_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->dropColumn(['notify_customer', 'notification_title', 'notification_body']);
        });

        Schema::table('service_stages', function (Blueprint $table) {
            $table->dropColumn(['notify_customer', 'notification_title', 'notification_body']);
        });
    }
};
