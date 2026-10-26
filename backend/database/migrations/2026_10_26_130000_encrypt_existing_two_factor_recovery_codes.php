<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('two_factor_recovery_codes')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'two_factor_recovery_codes' => Crypt::encryptString($user->two_factor_recovery_codes),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('two_factor_recovery_codes')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'two_factor_recovery_codes' => Crypt::decryptString($user->two_factor_recovery_codes),
                        ]);
                }
            });
    }
};
