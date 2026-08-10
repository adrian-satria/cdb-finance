<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_area', function (Blueprint $table) {
            $table->id();
            $table->string('kode_project', 10);
            $table->string('kode_area', 20);
            $table->unique(['kode_project', 'kode_area'], 'project_area_unique');
        });

        // migrate existing project.kode_area → project_area
        $projects = DB::table('project')->whereNotNull('kode_area')->get();
        foreach ($projects as $p) {
            $exists = DB::table('project_area')
                ->where('kode_project', $p->kode_project)
                ->where('kode_area', $p->kode_area)
                ->exists();
            if (!$exists) {
                DB::table('project_area')->insert([
                    'kode_project' => $p->kode_project,
                    'kode_area' => $p->kode_area,
                ]);
            }
        }

        Schema::table('project', function (Blueprint $table) {
            $table->dropColumn('kode_area');
        });
    }

    public function down(): void
    {
        Schema::table('project', function (Blueprint $table) {
            $table->string('kode_area', 20)->nullable()->after('nama_project');
        });

        // restore from pivot
        $pivots = DB::table('project_area')->get();
        foreach ($pivots as $pv) {
            DB::table('project')
                ->where('kode_project', $pv->kode_project)
                ->whereNull('kode_area')
                ->update(['kode_area' => $pv->kode_area]);
        }

        Schema::dropIfExists('project_area');
    }
};
