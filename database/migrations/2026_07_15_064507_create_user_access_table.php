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
        Schema::create('user_access', function (Blueprint $table) {
            $table->unsignedBigInteger('id_access', true);
            $table->unsignedBigInteger('id_user');
            $table->string('jabatan', 100)->nullable();
            $table->string('role', 50);
            $table->string('kode_area', 20)->nullable();
            $table->string('kode_project', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_access');
    }
};
