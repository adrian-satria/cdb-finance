<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area', function (Blueprint $table) {
            $table->string('kode_area', 20);
            $table->primary('kode_area');
            $table->string('nama_area', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area');
    }
};
