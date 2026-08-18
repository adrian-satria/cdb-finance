<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_uang_muka', function (Blueprint $table) {
            $table->string('no_aju', 50)->primary();
            $table->date('tanggal')->nullable();
            $table->integer('id_pengaju');
            $table->string('kode_project', 10);
            $table->string('kode_area', 10);
            $table->string('keterangan', 255)->nullable();
            $table->decimal('total_nominal', 15, 2)->default(0);
            $table->decimal('sisa_lpj', 15, 2)->default(0);
            $table->string('status_um', 30)->default('Pending');
            $table->string('posisi_saat_ini', 50)->default('MAKER');
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->string('keterangan_checker', 255)->nullable();
            $table->timestamps();
            $table->index(['kode_project', 'kode_area']);
            $table->index('posisi_saat_ini');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_uang_muka');
    }
};
