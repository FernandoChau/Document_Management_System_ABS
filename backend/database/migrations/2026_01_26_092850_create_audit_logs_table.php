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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // Nullable for system actions or public access
            $table->string('action'); // VIEW, DOWNLOAD, UPLOAD, SHARE, SOFT_DELETE, UPDATE_METADATA
            $table->string('resource_type'); // Document, Folder, etc.
            $table->uuid('resource_id');
            $table->json('metadata')->nullable(); // Additional info: IP, User Agent, changed fields
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for faster queries
            $table->index(['user_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
