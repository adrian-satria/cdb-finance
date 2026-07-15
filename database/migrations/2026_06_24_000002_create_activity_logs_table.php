<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('id_activity');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username');
            $table->string('role');
            $table->string('aktivitas');
            $table->text('deskripsi')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('created_at');
        });
    }
    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
