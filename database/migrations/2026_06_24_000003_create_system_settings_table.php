<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id('id_setting');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('label');
            $table->string('type')->default('text');
            $table->text('options')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('system_settings');
    }
};
