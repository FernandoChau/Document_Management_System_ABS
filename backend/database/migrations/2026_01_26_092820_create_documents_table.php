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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id');
            $table->string('name'); // Original filename
            $table->string('file_path'); // Physical storage path
            $table->string('reference_code')->unique(); // e.g., "dt.t.26.001"
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // File size in bytes
            $table->integer('year')->index(); // Year for reference code
            $table->integer('sequence_number'); // Sequential number per folder per year
            $table->uuid('user_id'); // Uploader
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index for faster queries
            $table->index(['folder_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
