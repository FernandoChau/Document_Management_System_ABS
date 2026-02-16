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
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('parent_id')->nullable();
            $table->uuid('department_id')->nullable(); // Only for root folders
            $table->string('reference_code')->unique(); // e.g., "dt.t"
            $table->boolean('is_root')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key to departments only (parent_id will be added after)
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
        
        // Add self-referencing foreign key after table creation
        Schema::table('folders', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
