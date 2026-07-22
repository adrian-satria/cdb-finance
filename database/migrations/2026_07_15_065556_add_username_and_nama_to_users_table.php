<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->after('id');
                $table->index('username');
            }
            if (! Schema::hasColumn('users', 'nama')) {
                $table->string('nama', 100)->nullable()->after('username');
            }
            if (! Schema::hasColumn('users', 'nama_lengkap')) {
                $table->string('nama_lengkap', 200)->nullable()->after('nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['username', 'nama', 'nama_lengkap'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
