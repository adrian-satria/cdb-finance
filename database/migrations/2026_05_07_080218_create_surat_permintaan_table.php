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
        Schema::create('surat_permintaan', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat'); // <--- PASTIKAN INI TULISANNYA no_surat
            $table->date('tanggal')->nullable();
            $table->string('bank_tujuan')->nullable();
            $table->string('no_rekening_tujuan')->nullable();
            $table->string('nama_rekening_tujuan')->nullable();
            $table->decimal('total_nominal', 15, 2)->default(0);
            $table->string('jenis_permintaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_permintaan');
    }
};
