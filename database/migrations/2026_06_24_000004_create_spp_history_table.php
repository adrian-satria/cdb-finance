<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_history', function (Blueprint $table) {
            $table->id('id_history');
            $table->string('no_surat');
            $table->string('status_dari')->nullable();
            $table->string('status_ke');
            $table->string('posisi_dari')->nullable();
            $table->string('posisi_ke');
            $table->string('aktor_username');
            $table->string('aktor_role');
            $table->text('keterangan')->nullable();
            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();
            $table->timestamps();
            $table->index('no_surat');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_history');
    }
};
