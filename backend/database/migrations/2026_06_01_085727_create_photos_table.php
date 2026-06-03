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
        Schema::create('photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('album_id')->constrained('albums')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('generated_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->foreign('cover_image_id')->references('id')->on('photos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropForeign(['cover_image_id']);
        });
        Schema::dropIfExists('photos');
    }
};
