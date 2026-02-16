<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->uuid('group_id')->nullable();
            $table->uuid('user_id')->nullable();

            // Permissions
            $table->boolean('can_view')->default(false);
            $table->boolean('can_update_metadata')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_download')->default(false);
            $table->boolean('can_share')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Constraint: group_id XOR user_id (não ambos null, não ambos filled)
            // Implementado em Model via check constraint ou validação
            $table->unique(['document_id', 'group_id', 'user_id'], 'document_permissions_unique');
            $table->index(['document_id', 'group_id']);
            $table->index(['document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_permissions');
    }
};
