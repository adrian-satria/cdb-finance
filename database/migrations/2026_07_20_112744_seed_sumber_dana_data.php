<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $data = [
            ['nama_rekening' => 'UPKM/CD RS BETHESDA.PO (BNI)',              'bank' => 'BNI',     'no_rekening' => '174883611'],
            ['nama_rekening' => 'YAKKUM.CDB.01 (PO)',                        'bank' => 'Mandiri', 'no_rekening' => '1370022498196'],
            ['nama_rekening' => 'YAKKUM.CDB.02 (LKP)',                       'bank' => 'Mandiri', 'no_rekening' => '1370022498204'],
            ['nama_rekening' => 'YAKKUM.CDB.03 (PK)',                        'bank' => 'Mandiri', 'no_rekening' => '1370022498220'],
            ['nama_rekening' => 'YAKKUM.CDB.04 (Griya Sehat)',               'bank' => 'Mandiri', 'no_rekening' => '1370022498238'],
            ['nama_rekening' => 'UPKM/CD Bethesda Batra',                    'bank' => 'BNI',     'no_rekening' => '279484890'],
            ['nama_rekening' => 'UPKM/CD Bethesda Yakkum TC',                'bank' => 'BNI',     'no_rekening' => '877273027'],
            ['nama_rekening' => 'UPKM/CD BETHESDA YAKKUM PROG BFDW',         'bank' => 'BNI',     'no_rekening' => '831715651'],
            ['nama_rekening' => 'YAKKUM CDB BFDW NTT',                       'bank' => 'BNI',     'no_rekening' => '1820743923'],
            ['nama_rekening' => 'YAKKUM BFDW HIV YAYASAN',                   'bank' => 'BNI',     'no_rekening' => '1914489020'],
            ['nama_rekening' => 'UPKM/CD BETHESDA YAKKUM PROGRAM',            'bank' => 'BNI',     'no_rekening' => '558878796'],
            ['nama_rekening' => 'UPKM/CD BETHESDA YAKKUM PROGRAM 2021',       'bank' => 'BNI',     'no_rekening' => '1286109356'],
            ['nama_rekening' => 'UPKM/CD BETHESDA YAKKUM PNN IDN20180027',    'bank' => 'BNI',     'no_rekening' => '692368102'],
        ];

        foreach ($data as $row) {
            DB::table('sumber_dana')->insert($row);
        }
    }

    public function down(): void
    {
        DB::table('sumber_dana')
            ->whereIn('no_rekening', [
                '174883611', '1370022498196', '1370022498204', '1370022498220',
                '1370022498238', '279484890', '877273027', '831715651',
                '1820743923', '1914489020', '558878796', '1286109356',
                '692368102',
            ])
            ->delete();
    }
};
