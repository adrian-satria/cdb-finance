<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_area', function (Blueprint $table) {
            $table->id();
            $table->string('kode_project', 10);
            $table->string('kode_area', 20);
            $table->string('kode_budget', 50);
            $table->string('nama_budget', 200)->nullable();
            $table->decimal('alokasi_dana', 15, 2)->default(0);
            $table->decimal('terserap', 15, 2)->default(0);
            $table->year('tahun')->nullable();
            $table->timestamps();
            $table->unique(['kode_project', 'kode_area', 'kode_budget', 'tahun'], 'budget_area_unique');
            $table->index(['kode_project', 'tahun']);
            $table->index('kode_budget');
            $table->index('kode_area');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_area');
    }
};
