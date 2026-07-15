<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'id_user')) {
                $table->unsignedBigInteger('id_user')->nullable()->after('id');
            }
        });

        // Copy existing id values to id_user
        if (Schema::hasColumn('users', 'id_user') && Schema::hasColumn('users', 'id')) {
            DB::statement('UPDATE users SET id_user = id WHERE id_user IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'id_user')) {
                $table->dropColumn('id_user');
            }
        });
    }
};
