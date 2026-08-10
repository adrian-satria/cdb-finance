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
        Schema::table('surat_permintaan', function (Blueprint $table) {
            $table->unique('no_surat');
            $table->index(['status_surat', 'posisi_saat_ini']);
            $table->index('kode_area');
            $table->index('kode_project');
        });

        Schema::table('surat_permintaan_detail', function (Blueprint $table) {
            $table->index('no_surat');
        });

        Schema::table('surat_permintaan_files', function (Blueprint $table) {
            $table->index('no_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_permintaan', function (Blueprint $table) {
            $table->dropUnique(['no_surat']);
            $table->dropIndex(['status_surat', 'posisi_saat_ini']);
            $table->dropIndex(['kode_area']);
            $table->dropIndex(['kode_project']);
        });

        Schema::table('surat_permintaan_detail', function (Blueprint $table) {
            $table->dropIndex(['no_surat']);
        });

        Schema::table('surat_permintaan_files', function (Blueprint $table) {
            $table->dropIndex(['no_surat']);
        });
    }
};
