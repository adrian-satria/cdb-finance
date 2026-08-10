<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SppNumberGeneratorService
{
    /**
     * Generate the next SPP number (per-project monthly sequence).
     * Format: YYYY/RomanMonth/SPP/KodeProject/NNN
     * Atomic increment via spp_sequences table to prevent collision.
     *
     * @param  string  $tanggal  Date in Y-m-d format
     * @param  string  $kodeProject  Project code (shown in number, sequence per project/month)
     * @return string Generated SPP number
     */
    public function generateNextNumber(string $tanggal, string $kodeProject = 'XX'): string
    {
        $tahun = (int) date('Y', strtotime($tanggal));
        $bulanIndex = (int) date('n', strtotime($tanggal));
        $bulanRomawi = $this->getRomanMonth($bulanIndex);

        $noUrut = DB::transaction(function () use ($tahun, $bulanIndex, $kodeProject) {
            $seq = DB::table('spp_sequences')
                ->where('tahun', $tahun)
                ->where('bulan', $bulanIndex)
                ->where('kode_project', $kodeProject)
                ->lockForUpdate()
                ->first();

            if ($seq) {
                $nextNum = $seq->last_number + 1;
                DB::table('spp_sequences')
                    ->where('id', $seq->id)
                    ->update(['last_number' => $nextNum]);
            } else {
                DB::table('spp_sequences')->insert([
                    'tahun' => $tahun,
                    'bulan' => $bulanIndex,
                    'kode_project' => $kodeProject,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $nextNum = 1;
            }

            return $nextNum;
        });

        return $tahun.'/'.$bulanRomawi."/SPP/{$kodeProject}/".str_pad($noUrut, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get Roman numeral for month.
     *
     * @param  int  $month  Month number (1-12)
     * @return string Roman numeral
     */
    public function getRomanMonth(int $month): string
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romanMonths[$month] ?? 'I';
    }

    /**
     * Parse SPP number to extract components.
     *
     * @return array ['tahun', 'bulan', 'kode_project', 'no_urut']
     */
    public function parseNumber(string $noSurat): array
    {
        // Format: 2026/VII/SPP/GLOBAL/015
        $parts = explode('/', $noSurat);

        return [
            'tahun' => $parts[0] ?? null,
            'bulan' => $parts[1] ?? null,
            'kode_project' => $parts[3] ?? null,
            'no_urut' => isset($parts[4]) ? (int) $parts[4] : null,
        ];
    }

    /**
     * Get next sequence number for a given project, year, and month.
     *
     * @return int Next sequence number
     */
    public function getNextSequence(int $tahun, int $bulan, string $kodeProject = 'XX'): int
    {
        $seq = DB::table('spp_sequences')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('kode_project', $kodeProject)
            ->first();

        return ($seq ? $seq->last_number : 0) + 1;
    }

    /**
     * Generate preview SPP number for display (no locking).
     * Safe to use in GET requests.
     *
     * @param  string  $tanggal  Date in Y-m-d format
     * @param  string  $kodeProject  Project code
     * @return string Preview SPP number (may differ from actual on save)
     */
    public function generatePreviewNumber(string $tanggal, string $kodeProject = 'XX'): string
    {
        $tahun = (int) date('Y', strtotime($tanggal));
        $bulanIndex = (int) date('n', strtotime($tanggal));
        $bulanRomawi = $this->getRomanMonth($bulanIndex);
        $nextSeq = $this->getNextSequence($tahun, $bulanIndex, $kodeProject);

        return $tahun.'/'.$bulanRomawi."/SPP/{$kodeProject}/".str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Validate SPP number format.
     *
     * @return bool True if valid format
     */
    public function isValidFormat(string $noSurat): bool
    {
        // Format: YYYY/RomanMonth/SPP/KodeProject/NNN
        // Example: 2026/VII/SPP/40/015
        $pattern = '/^\d{4}\/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)\/SPP\/[A-Za-z0-9_-]+\/\d{3}$/';

        return preg_match($pattern, $noSurat) === 1;
    }
}
