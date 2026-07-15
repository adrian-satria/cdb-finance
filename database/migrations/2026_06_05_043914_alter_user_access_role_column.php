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
        Schema::table('user_access', function (Blueprint $table) {
            // Ubah kolom 'role' menjadi VARCHAR(50)
            $table->string('role', 50)->change();
            // Jika Anda juga ingin mengubah 'jabatan' karena mungkin ada nilai panjang
            // $table->string('jabatan', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_access', function (Blueprint $table) {
            // Kembalikan ke ukuran semula jika diperlukan (misal: VARCHAR(20))
            $table->string('role', 20)->change();
            // $table->string('jabatan', 50)->change();
        });
    }
};

