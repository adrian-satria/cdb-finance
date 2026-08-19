<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpj_uang_muka_detail', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('kode_budget')->comment('Tanggal realisasi item (untuk form LPJ)');
            $table->string('no_bukti', 100)->nullable()->after('nominal')->comment('Referensi halaman lampiran bukti');
        });
    }

    public function down(): void
    {
        Schema::table('lpj_uang_muka_detail', function (Blueprint $table) {
            $table->dropColumn(['tanggal', 'no_bukti']);
        });
    }
};
