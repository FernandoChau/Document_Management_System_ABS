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
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID como chave primária
            $table->string('name');
            $table->string('path')->nullable();
            $table->string('link')->nullable();
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->uuid('folder_id')->nullable();
            $table->enum('tag', ['Important', 'Relevant', 'Optional'])->default('Optional');
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_accessible')->default(true);
            $table->boolean('is_removable')->default(true);
            $table->boolean('is_removed')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')
                ->references('id')
                ->on('folders')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
        Schema::table('files', function (Blueprint $table) {
            $table->unique(['name', 'folder_id'], 'unique_name_folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            // Remover a constraint única primeiro
            $table->dropUnique('unique_name_folder');

            // Remover as chaves estrangeiras
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('files');
    }
};
