<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('creates_orchard_on_completion')->default(false)->after('is_active');
        });

        // Set creates_orchard_on_completion = true for orchard establishment services
        DB::table('services')
            ->where('slug', 'like', '%orchard%')
            ->orWhere('category', 'like', '%orchard-development%')
            ->update(['creates_orchard_on_completion' => true]);
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('creates_orchard_on_completion');
        });
    }
};
