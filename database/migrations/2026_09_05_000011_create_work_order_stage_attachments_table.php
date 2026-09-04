<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_stage_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_stage_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('photo');
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_stage_attachments');
    }
};