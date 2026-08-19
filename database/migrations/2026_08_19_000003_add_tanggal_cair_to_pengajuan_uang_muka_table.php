<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_uang_muka', function (Blueprint $table) {
            $table->date('tanggal_cair')->nullable()->after('tanggal_jatuh_tempo')
                ->comment('Tanggal UM dicairkan (untuk register / umur kas bon)');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_uang_muka', function (Blueprint $table) {
            $table->dropColumn('tanggal_cair');
        });
    }
};
