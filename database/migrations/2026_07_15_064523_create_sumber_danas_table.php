<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumber_dana', function (Blueprint $table) {
            $table->unsignedBigInteger('id_bank_kas', true);
            $table->string('nama_rekening', 100);
            $table->string('no_rekening', 50)->nullable();
            $table->string('bank', 100)->nullable();
            $table->string('jenis', 20)->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_dana');
    }
};
