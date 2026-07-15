<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_permintaan_detail', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat', 50);
            $table->string('keterangan', 255)->default('-');
            $table->string('kode_budget', 50);
            $table->decimal('nominal', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_permintaan_detail');
    }
};
