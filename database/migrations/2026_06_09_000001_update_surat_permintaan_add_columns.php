<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_permintaan', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_permintaan', 'kode_project')) {
                $table->string('kode_project', 10)->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'kode_area')) {
                $table->string('kode_area', 20)->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'sumber_dana')) {
                $table->string('sumber_dana', 100)->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'total_nominal')) {
                $table->decimal('total_nominal', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('surat_permintaan', 'status_surat')) {
                $table->string('status_surat', 50)->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'posisi_saat_ini')) {
                $table->string('posisi_saat_ini', 50)->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'id_maker')) {
                $table->unsignedBigInteger('id_maker')->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'keterangan_checker')) {
                $table->text('keterangan_checker')->nullable();
            }

            if (! Schema::hasColumn('surat_permintaan', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_permintaan', function (Blueprint $table) {
            if (Schema::hasColumn('surat_permintaan', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('surat_permintaan', 'keterangan_checker')) {
                $table->dropColumn('keterangan_checker');
            }
            if (Schema::hasColumn('surat_permintaan', 'id_maker')) {
                $table->dropColumn('id_maker');
            }
            if (Schema::hasColumn('surat_permintaan', 'posisi_saat_ini')) {
                $table->dropColumn('posisi_saat_ini');
            }
            if (Schema::hasColumn('surat_permintaan', 'status_surat')) {
                $table->dropColumn('status_surat');
            }
            if (Schema::hasColumn('surat_permintaan', 'total_nominal')) {
                $table->dropColumn('total_nominal');
            }
            if (Schema::hasColumn('surat_permintaan', 'sumber_dana')) {
                $table->dropColumn('sumber_dana');
            }
        });
    }
};
