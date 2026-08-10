<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_sequences', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->string('kode_project', 10);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'kode_project'], 'spp_seq_unique');
            $table->index(['tahun', 'kode_project']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_sequences');
    }
};
