<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class SppNumberGeneratorService
{
    public function __construct(
        protected string $sppPrefix = 'PROJECT-X'
    ) {}

    public function setSppPrefix(string $prefix): void
    {
        $this->sppPrefix = $prefix;
    }

    /**
     * Generate the next SPP number based on date.
     * Format: YYYY/MM/SPP/PROJECT-X/NNN
     *
     * @param  string  $tanggal  Date in Y-m-d format
     * @return string Generated SPP number
     *
     * @throws Exception if collision detected
     */
    public function generateNextNumber(string $tanggal): string
    {
        $tahun = (int) date('Y', strtotime($tanggal));
        $bulanIndex = (int) date('n', strtotime($tanggal));
        $bulanRomawi = $this->getRomanMonth($bulanIndex);

        $terakhir = DB::table('surat_permintaan')
            ->whereYear('created_at', $tahun)
            ->whereRaw('MONTH(created_at) = ?', [$bulanIndex])
            ->orderBy('created_at', 'desc')
            ->lockForUpdate()
            ->first();

        if ($terakhir && ! empty($terakhir->no_surat)) {
            $noUrut = (int) substr($terakhir->no_surat, -3) + 1;
        } else {
            $noUrut = 1;
        }

        $nomorBaru = $tahun.'/'.$bulanRomawi."/SPP/{$this->sppPrefix}/".str_pad($noUrut, 3, '0', STR_PAD_LEFT);

        if ($this->checkCollision($nomorBaru)) {
            throw new Exception("Collision detected for SPP number: {$nomorBaru}");
        }

        return $nomorBaru;
    }

    /**
     * Check if SPP number already exists.
     *
     * @return bool True if exists, false otherwise
     */
    public function checkCollision(string $noSurat): bool
    {
        return DB::table('surat_permintaan')
            ->where('no_surat', $noSurat)
            ->exists();
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
     * @return array ['tahun', 'bulan', 'no_urut']
     */
    public function parseNumber(string $noSurat): array
    {
        // Format: 2026/VII/SPP/PROJECT-X/015
        $parts = explode('/', $noSurat);

        return [
            'tahun' => $parts[0] ?? null,
            'bulan' => $parts[1] ?? null,
            'no_urut' => isset($parts[4]) ? (int) $parts[4] : null,
        ];
    }

    /**
     * Get next sequence number for a given month.
     *
     * @return int Next sequence number
     */
    public function getNextSequence(int $tahun, int $bulan): int
    {
        $terakhir = DB::table('surat_permintaan')
            ->whereYear('created_at', $tahun)
            ->whereRaw('MONTH(created_at) = ?', [$bulan])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($terakhir && ! empty($terakhir->no_surat)) {
            return (int) substr($terakhir->no_surat, -3) + 1;
        }

        return 1;
    }

    /**
     * Generate preview SPP number for display (no locking).
     * Safe to use in GET requests.
     *
     * @param  string  $tanggal  Date in Y-m-d format
     * @return string Preview SPP number (may differ from actual on save)
     */
    public function generatePreviewNumber(string $tanggal): string
    {
        $tahun = (int) date('Y', strtotime($tanggal));
        $bulanIndex = (int) date('n', strtotime($tanggal));
        $bulanRomawi = $this->getRomanMonth($bulanIndex);
        $nextSeq = $this->getNextSequence($tahun, $bulanIndex);

        return $tahun.'/'.$bulanRomawi."/SPP/{$this->sppPrefix}/".str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Validate SPP number format.
     *
     * @return bool True if valid format
     */
    public function isValidFormat(string $noSurat): bool
    {
        // Format: YYYY/RomanMonth/SPP/PROJECT-X/NNN
        // Example: 2026/VII/SPP/PROJECT-X/015
        $escapedPrefix = preg_quote($this->sppPrefix, '/');
        $pattern = '/^\d{4}\/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)\/SPP\/'.$escapedPrefix.'\/\d{3}$/';

        return preg_match($pattern, $noSurat) === 1;
    }
}
