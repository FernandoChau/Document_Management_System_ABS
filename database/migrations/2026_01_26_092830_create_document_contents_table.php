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
        Schema::create('document_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id')->unique();
            $table->longText('extracted_text')->nullable();
            $table->enum('extraction_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_contents');
    }
};
