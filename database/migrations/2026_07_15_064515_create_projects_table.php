<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project', function (Blueprint $table) {
            $table->string('kode_project', 10);
            $table->primary('kode_project');
            $table->string('nama_project', 100);
            $table->string('kode_area', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};
