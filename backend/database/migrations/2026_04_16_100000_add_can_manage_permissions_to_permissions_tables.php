<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add can_manage_permissions to folder_permissions table
        Schema::table('folder_permissions', function (Blueprint $table) {
            $table->boolean('can_manage_permissions')->default(false)->after('can_download');
        });

        // Add can_manage_permissions to document_permissions table
        Schema::table('document_permissions', function (Blueprint $table) {
            $table->boolean('can_manage_permissions')->default(false)->after('can_share');
        });
    }

    public function down(): void
    {
        Schema::table('folder_permissions', function (Blueprint $table) {
            $table->dropColumn('can_manage_permissions');
        });

        Schema::table('document_permissions', function (Blueprint $table) {
            $table->dropColumn('can_manage_permissions');
        });
    }
};
