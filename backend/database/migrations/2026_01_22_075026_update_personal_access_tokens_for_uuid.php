<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Esta migração foi removida pois causava conflitos no rollback
        // Os tokens de acesso pessoal usam UUIDs agora
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nenhuma ação necessária no rollback
    }
};
