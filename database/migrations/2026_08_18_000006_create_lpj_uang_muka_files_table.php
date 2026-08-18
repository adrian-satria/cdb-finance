<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_uang_muka_files', function (Blueprint $table) {
            $table->id();
            $table->string('no_lpj', 50);
            $table->string('nama_file', 255);
            $table->string('kategori', 20)->default('MAKER');
            $table->string('tipe_file', 10)->nullable();
            $table->index('no_lpj');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_uang_muka_files');
    }
};
