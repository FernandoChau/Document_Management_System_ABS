<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });

        Schema::table('document_permissions', function (Blueprint $table) {
            $table->boolean('is_inherited')->default(false);
            $table->boolean('is_revoked')->default(false);
        });
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'image_manager')->update(['role' => 'user']);

        Schema::table('document_permissions', function (Blueprint $table) {
            $table->dropColumn(['is_inherited', 'is_revoked']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->change();
        });
    }
};
