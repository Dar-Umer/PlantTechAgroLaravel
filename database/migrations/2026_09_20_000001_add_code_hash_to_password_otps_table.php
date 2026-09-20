<?php

use App\Models\PasswordOtp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_otps', function (Blueprint $table) {
            $table->string('code_hash', 255)->nullable()->after('code');
        });

        // Backfill hashes for any live plaintext codes so verification
        // keeps working across the deploy. Expired rows die naturally.
        PasswordOtp::query()
            ->whereNull('code_hash')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->chunkById(100, function ($otps) {
                foreach ($otps as $otp) {
                    $otp->forceFill(['code_hash' => Hash::make($otp->code)])->save();
                }
            });
    }

    public function down(): void
    {
        Schema::table('password_otps', function (Blueprint $table) {
            $table->dropColumn('code_hash');
        });
    }
};
