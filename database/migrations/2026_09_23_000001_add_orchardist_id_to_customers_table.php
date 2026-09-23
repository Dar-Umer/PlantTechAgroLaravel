<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('orchardist_id')->nullable()->unique()->after('id');
        });

        // Backfill existing customers with unique sequential OID numbers
        $customers = DB::table('customers')->orderBy('id')->get(['id']);
        $seq = 1001;
        foreach ($customers as $c) {
            DB::table('customers')->where('id', $c->id)->update([
                'orchardist_id' => sprintf('OID-%04d', $seq),
            ]);
            $seq++;
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('orchardist_id');
        });
    }
};
