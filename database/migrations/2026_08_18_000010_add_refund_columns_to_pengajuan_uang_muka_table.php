<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_uang_muka', function (Blueprint $table) {
            $table->decimal('refund_jumlah', 15, 2)->nullable()->after('sisa_lpj');
            $table->date('refund_tanggal')->nullable()->after('refund_jumlah');
            $table->string('refund_bukti', 255)->nullable()->after('refund_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_uang_muka', function (Blueprint $table) {
            $table->dropColumn(['refund_jumlah', 'refund_tanggal', 'refund_bukti']);
        });
    }
};