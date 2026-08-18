<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SequenceNumberService
{
    public function generate(string $tanggal, string $kodeProject, string $prefix): string
    {
        $tahun = (int) date('Y', strtotime($tanggal));
        $bulan = (int) date('n', strtotime($tanggal));
        $roman = $this->roman($bulan);

        $no = DB::transaction(function () use ($tahun, $bulan, $kodeProject, $prefix) {
            $seq = DB::table('document_sequences')
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->where('kode_project', $kodeProject)
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if ($seq) {
                $next = $seq->last_number + 1;
                DB::table('document_sequences')
                    ->where('id', $seq->id)
                    ->update(['last_number' => $next]);
            } else {
                DB::table('document_sequences')->insert([
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'kode_project' => $kodeProject,
                    'prefix' => $prefix,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $next = 1;
            }

            return $next;
        });

        return $tahun.'/'.$roman.'/'.$prefix.'/'.$kodeProject.'/'.str_pad($no, 3, '0', STR_PAD_LEFT);
    }

    private function roman(int $month): string
    {
        $roman = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $roman[$month] ?? 'I';
    }
}
