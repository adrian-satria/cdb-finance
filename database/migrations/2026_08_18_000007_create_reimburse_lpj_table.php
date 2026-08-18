<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reimburse_lpj', function (Blueprint $table) {
            $table->string('no_reimburse', 50)->primary();
            $table->string('no_lpj', 50);
            $table->string('no_aju', 50);
            $table->decimal('total_nominal', 15, 2)->default(0);
            $table->string('status_reimburse', 30)->default('Pending');
            $table->string('posisi_saat_ini', 50)->default('MANAGER_KEUANGAN');
            $table->timestamps();
            $table->index('no_lpj');
            $table->index('no_aju');
            $table->index('posisi_saat_ini');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimburse_lpj');
    }
};
