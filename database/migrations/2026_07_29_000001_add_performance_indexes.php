<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_budget', function (Blueprint $table) {
            $table->index('kode_budget');
            $table->index(['kode_project', 'tahun']);
        });

        Schema::table('user_access', function (Blueprint $table) {
            $table->index(['id_user', 'role']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at'], 'notif_user_read_idx');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->index('created_at', 'audit_created_idx');
            $table->index(['username', 'role', 'aksi'], 'audit_user_role_aksi_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['username', 'role', 'aktivitas'], 'activity_user_role_akt_idx');
        });

        Schema::table('surat_permintaan', function (Blueprint $table) {
            $table->index('tanggal', 'sp_tanggal_idx');
            $table->index('total_nominal', 'sp_nominal_idx');
        });
    }

    public function down(): void
    {
        Schema::table('master_budget', function (Blueprint $table) {
            $table->dropIndex(['kode_budget']);
            $table->dropIndex(['kode_project', 'tahun']);
        });

        Schema::table('user_access', function (Blueprint $table) {
            $table->dropIndex(['id_user', 'role']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_user_read_idx');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropIndex('audit_created_idx');
            $table->dropIndex('audit_user_role_aksi_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_user_role_akt_idx');
        });

        Schema::table('surat_permintaan', function (Blueprint $table) {
            $table->dropIndex('sp_tanggal_idx');
            $table->dropIndex('sp_nominal_idx');
        });
    }
};
