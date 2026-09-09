<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterBudgetSeeder extends Seeder
{
    public function run(): void
    {
        $budgets = [
            // DEMO-01 - Community Development Program
            [
                'kode_project' => 'DEMO-01',
                'kode_budget' => '1.0',
                'nama_budget' => 'Program Activities',
                'alokasi_dana' => 75000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-01',
                'kode_budget' => '1.1',
                'nama_budget' => 'Community Training',
                'alokasi_dana' => 25000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-01',
                'kode_budget' => '1.2',
                'nama_budget' => 'Community Workshop',
                'alokasi_dana' => 20000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-01',
                'kode_budget' => '1.3',
                'nama_budget' => 'Monitoring and Evaluation',
                'alokasi_dana' => 15000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-01',
                'kode_budget' => '1.4',
                'nama_budget' => 'Communication and Documentation',
                'alokasi_dana' => 15000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],

            // DEMO-02 - Digital Transformation Program
            [
                'kode_project' => 'DEMO-02',
                'kode_budget' => '1.0',
                'nama_budget' => 'Digital Transformation',
                'alokasi_dana' => 100000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-02',
                'kode_budget' => '1.1',
                'nama_budget' => 'Software Development',
                'alokasi_dana' => 40000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-02',
                'kode_budget' => '1.2',
                'nama_budget' => 'Infrastructure and Equipment',
                'alokasi_dana' => 30000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-02',
                'kode_budget' => '1.3',
                'nama_budget' => 'Training and Knowledge Transfer',
                'alokasi_dana' => 15000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
            [
                'kode_project' => 'DEMO-02',
                'kode_budget' => '1.4',
                'nama_budget' => 'Maintenance and Support',
                'alokasi_dana' => 15000000,
                'terserap' => 0,
                'tahun' => 2026,
            ],
        ];

        foreach ($budgets as $budget) {
            DB::table('master_budget')->updateOrInsert(
                [
                    'kode_project' => $budget['kode_project'],
                    'kode_budget' => $budget['kode_budget'],
                    'tahun' => $budget['tahun'],
                ],
                $budget
            );
        }
    }
}