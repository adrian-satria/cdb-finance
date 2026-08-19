<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_archives', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 100);
            $table->string('record_key', 100)->nullable()->comment('primary key value of archived row');
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->longText('payload')->comment('full row snapshot (JSON)');
            $table->string('archive_file', 255)->nullable()->comment('path to gzipped JSON export');
            $table->timestamp('original_created_at')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'record_key']);
        });

        Schema::create('archive_batches', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100)->nullable();
            $table->string('table_name', 100);
            $table->unsignedInteger('rows')->default(0);
            $table->string('archive_file', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_batches');
        Schema::dropIfExists('data_archives');
    }
};
