<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Change tokenable_id from integer to UUID
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE uuid USING NULL');
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id DROP NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't convert back to bigint - keep as UUID for consistency
        // This prevents errors with existing UUID data
    }
};
