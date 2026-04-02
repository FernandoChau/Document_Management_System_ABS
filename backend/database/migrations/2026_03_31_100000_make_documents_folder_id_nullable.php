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
        Schema::table('documents', function (Blueprint $table) {
            // Drop the existing foreign key before altering
            $table->dropForeign(['folder_id']);
            
            // Make folder_id nullable (for root documents)
            $table->uuid('folder_id')->nullable()->change();
            
            // Re-add the foreign key with onDelete cascade
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['folder_id']);
            
            // Restore folder_id as non-nullable
            $table->uuid('folder_id')->nullable(false)->change();
            
            // Re-add the foreign key
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }
};
