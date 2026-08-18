<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_uang_muka', function (Blueprint $table) {
            $table->string('no_lpj', 50)->primary();
            $table->string('no_aju', 50);
            $table->integer('id_pelaksana');
            $table->date('tanggal')->nullable();
            $table->decimal('total_realisasi', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->string('status_lpj', 30)->default('Pending');
            $table->string('posisi_saat_ini', 50)->default('MAKER');
            $table->string('keterangan_checker', 255)->nullable();
            $table->timestamps();
            $table->index('no_aju');
            $table->index('posisi_saat_ini');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_uang_muka');
    }
};
