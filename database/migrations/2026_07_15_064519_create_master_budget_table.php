<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_budget', function (Blueprint $table) {
            $table->unsignedBigInteger('id_budget', true);
            $table->string('kode_project', 10)->nullable();
            $table->string('kode_budget', 50);
            $table->string('nama_budget', 200);
            $table->decimal('alokasi_dana', 15, 2)->default(0);
            $table->decimal('terserap', 15, 2)->default(0);
            $table->year('tahun')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_budget');
    }
};
