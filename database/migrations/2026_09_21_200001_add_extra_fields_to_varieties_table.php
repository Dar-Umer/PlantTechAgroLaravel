<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('varieties', function (Blueprint $table) {
            if (! Schema::hasColumn('varieties', 'origin')) {
                $table->string('origin')->nullable()->after('taste');
            }
            if (! Schema::hasColumn('varieties', 'color')) {
                $table->string('color')->nullable()->after('origin');
            }
            if (! Schema::hasColumn('varieties', 'storage_life')) {
                $table->string('storage_life')->nullable()->after('color');
            }
            if (! Schema::hasColumn('varieties', 'description')) {
                $table->longText('description')->nullable()->after('short_description');
            }
            if (! Schema::hasColumn('varieties', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('varieties', function (Blueprint $table) {
            $table->dropColumn(['origin', 'color', 'storage_life', 'description', 'is_featured']);
        });
    }
};
