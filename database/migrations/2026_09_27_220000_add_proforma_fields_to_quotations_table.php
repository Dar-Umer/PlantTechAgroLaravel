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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('scope_title')->nullable()->after('service_id');
            $table->string('scope_subtitle')->nullable()->after('scope_title');
            $table->string('variety_name')->nullable()->after('scope_subtitle');
            $table->string('variety_specification')->nullable()->after('variety_name');
            $table->string('rootstock')->nullable()->after('variety_specification');
            $table->string('plants_per_kanal')->nullable()->after('rootstock');
            $table->string('package_title')->nullable()->after('plants_per_kanal');
            $table->integer('package_poles')->nullable()->after('package_title');
            $table->integer('package_anchors')->nullable()->after('package_poles');
            $table->integer('package_plants')->nullable()->after('package_anchors');
            $table->json('payment_schedule')->nullable()->after('package_plants');
            $table->text('additional_notes')->nullable()->after('notes');
            $table->string('bank_name')->nullable()->after('terms');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_no')->nullable()->after('bank_account_name');
            $table->string('bank_branch')->nullable()->after('bank_account_no');
            $table->string('bank_ifsc')->nullable()->after('bank_branch');
            $table->string('company_address')->nullable()->after('bank_ifsc');
            $table->string('company_phone')->nullable()->after('company_address');
            $table->string('company_email')->nullable()->after('company_phone');
            $table->string('company_website')->nullable()->after('company_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'scope_title',
                'scope_subtitle',
                'variety_name',
                'variety_specification',
                'rootstock',
                'plants_per_kanal',
                'package_title',
                'package_poles',
                'package_anchors',
                'package_plants',
                'payment_schedule',
                'additional_notes',
                'bank_name',
                'bank_account_name',
                'bank_account_no',
                'bank_branch',
                'bank_ifsc',
                'company_address',
                'company_phone',
                'company_email',
                'company_website',
            ]);
        });
    }
};
