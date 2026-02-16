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
        Schema::table('share_links', function (Blueprint $table) {
            $table->integer('max_downloads')->nullable()->after('password');
            $table->integer('downloads_count')->default(0)->after('max_downloads');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_links', function (Blueprint $table) {
            $table->dropColumn(['max_downloads', 'downloads_count']);
        });
    }
};
