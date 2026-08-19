<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah created_at/updated_at agar tabel lampiran ikut masuk retensi
        // (data:archive memindahkan file fisik ke cold storage setelah lewat masa retensi).
        foreach ([
            'surat_permintaan_files',
            'pengajuan_uang_muka_files',
            'lpj_uang_muka_files',
            'reimburse_lpj_files',
        ] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'created_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'surat_permintaan_files',
            'pengajuan_uang_muka_files',
            'lpj_uang_muka_files',
            'reimburse_lpj_files',
        ] as $table) {
            if (Schema::hasColumn($table, 'created_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropTimestamps();
                });
            }
        }
    }
};
