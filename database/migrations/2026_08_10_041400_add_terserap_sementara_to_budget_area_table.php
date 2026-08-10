<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_area', function (Blueprint $table) {
            $table->decimal('terserap_sementara', 15, 2)->default(0)->after('terserap');
        });
    }

    public function down(): void
    {
        Schema::table('budget_area', function (Blueprint $table) {
            $table->dropColumn('terserap_sementara');
        });
    }
};
