<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID como chave primária
            $table->string('name');
            $table->string('link')->nullable();
            $table->uuid('parent_id')->nullable(); // UUID para parent_id
            $table->enum('tag', ['Important', 'Relevant', 'Optional'])->default('Optional');
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_accessible')->default(true);
            $table->boolean('is_removable')->default(true);
            $table->boolean('is_removed')->default(false);
            $table->boolean('is_public')->default(false);
            // $table->boolean('is_removed')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('expires_at')->nullable();    
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('parent_id')
                ->references('id')
                ->on('folders')
                ->onDelete('cascade');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->unique(['name', 'parent_id'], 'unique_name_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('folders', function (Blueprint $table) {
            // Remover a constraint única primeiro
            $table->dropUnique('unique_name_parent');

            // Remover as chaves estrangeiras
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['created_by']);
        });

        // Remover a tabela completamente
        Schema::dropIfExists('folders');
    }
};
