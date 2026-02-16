<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folder_responsibles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id');
            $table->uuid('user_id');
            $table->boolean('is_owner')->default(true);
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['folder_id', 'user_id']);
            $table->index(['folder_id', 'is_owner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_responsibles');
    }
};
