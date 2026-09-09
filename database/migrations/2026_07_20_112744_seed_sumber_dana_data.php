<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $data = [
            [
                'nama_rekening' => 'Demo Operating Account',
                'bank' => 'Demo Bank',
                'no_rekening' => '0000000001',
            ],
            [
                'nama_rekening' => 'Demo Program Account',
                'bank' => 'Demo Bank',
                'no_rekening' => '0000000002',
            ],
            [
                'nama_rekening' => 'Demo Project Account',
                'bank' => 'Demo Bank',
                'no_rekening' => '0000000003',
            ],
            [
                'nama_rekening' => 'Demo Community Account',
                'bank' => 'Demo Bank',
                'no_rekening' => '0000000004',
            ],
        ];

        foreach ($data as $row) {
            DB::table('sumber_dana')->insert($row);
        }
    }

    public function down(): void
    {
        DB::table('sumber_dana')
            ->whereIn('no_rekening', [
                '0000000001',
                '0000000002',
                '0000000003',
                '0000000004',
            ])
            ->delete();
    }
};