<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folder_permissions', function (Blueprint $table) {
            // Adicionar coluna de grupo
            $table->uuid('group_id')->nullable()->after('folder_id');

            // Adicionar colunas de permissões detalhadas
            $table->boolean('can_view')->default(true)->after('group_id');
            $table->boolean('can_update_metadata')->default(false)->after('can_view');
            $table->boolean('can_delete')->default(false)->after('can_update_metadata');
            $table->boolean('can_upload')->default(false)->after('can_delete');
            $table->boolean('can_share')->default(false)->after('can_upload');
            $table->boolean('can_download')->default(true)->after('can_share');

            // Dropar coluna antiga permission_level (se existir)
            if (Schema::hasColumn('folder_permissions', 'permission_level')) {
                $table->dropColumn('permission_level');
            }

            // Adicionar foreign key para grupo
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');

            // Adicionar índices
            $table->index(['folder_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::table('folder_permissions', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['folder_id', 'group_id']);

            // Remover colunas novas
            $table->dropColumn([
                'group_id',
                'can_view',
                'can_update_metadata',
                'can_delete',
                'can_upload',
                'can_share',
                'can_download'
            ]);

            // Restaurar coluna antiga (opcional)
            $table->enum('permission_level', ['view', 'edit', 'manage'])->default('view');
        });
    }
};
