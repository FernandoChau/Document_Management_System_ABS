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
        Schema::create('shared_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->uuidMorphs('shareable'); // shareable_type, shareable_id (UUID) 
            $table->timestamp('expires_at')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_links');
    }
};
