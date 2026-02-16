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
        Schema::create('share_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token')->unique()->index();
            $table->string('shareable_type'); // Polymorphic: Document or Folder
            $table->uuid('shareable_id');
            $table->uuid('created_by'); // User who created the share link
            $table->timestamp('expires_at')->nullable();
            $table->string('password')->nullable(); // Hashed password
            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Index for polymorphic relation
            $table->index(['shareable_type', 'shareable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_links');
    }
};
