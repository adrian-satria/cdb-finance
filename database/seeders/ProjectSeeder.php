<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'kode_project' => 'DEMO-01',
                'nama_project' => 'Community Development Program',
            ],
            [
                'kode_project' => 'DEMO-02',
                'nama_project' => 'Digital Transformation Program',
            ],
        ];

        foreach ($projects as $project) {
            DB::table('project')->updateOrInsert(
                ['kode_project' => $project['kode_project']],
                $project
            );
        }
    }
}