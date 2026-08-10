<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_permintaan', function (Blueprint $table) {
            $table->decimal('biaya_admin', 15, 2)->default(0)->after('total_nominal');
        });
    }

    public function down(): void
    {
        Schema::table('surat_permintaan', function (Blueprint $table) {
            $table->dropColumn('biaya_admin');
        });
    }
};
