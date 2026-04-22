<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete any orphaned documents (no parent folder)
        DB::table('documents')->whereNull('folder_id')->delete();

        Schema::table('documents', function (Blueprint $table) {
            // Drop the existing foreign key before altering
            $table->dropForeign(['folder_id']);
            
            // Make folder_id NOT NULL (documents must have a parent folder)
            $table->uuid('folder_id')->nullable(false)->change();
            
            // Re-add the foreign key with onDelete cascade
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');

            // Ensure index exists for performance
            $table->index('folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropIndex(['folder_id']);
            
            // Revert to nullable
            $table->uuid('folder_id')->nullable()->change();
            
            // Re-add the foreign key
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }
};
