<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_permintaan_files', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat', 50);
            $table->string('nama_file', 255);
            $table->string('kategori', 20)->default('MAKER');
            $table->string('tipe_file', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_permintaan_files');
    }
};
