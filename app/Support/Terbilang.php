<?php

namespace App\Support;

/**
 * Konversi angka ke terbilang (rupiah) untuk cetakan form.
 */
class Terbilang
{
    public static function rp($number): string
    {
        $number = (int) round((float) $number);

        if ($number < 0) {
            return 'minus '.self::words(abs($number));
        }

        if ($number === 0) {
            return 'nol rupiah';
        }

        return self::words($number).' rupiah';
    }

    private static function words(int $n): string
    {
        $abil = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
            'sepuluh', 'sebelas'];

        if ($n < 12) {
            return $abil[$n];
        }
        if ($n < 20) {
            return self::words($n - 10).' belas';
        }
        if ($n < 100) {
            return trim(self::words((int) ($n / 10)).' puluh '.self::words($n % 10));
        }
        if ($n < 200) {
            return trim('seratus '.self::words($n - 100));
        }
        if ($n < 1000) {
            return trim(self::words((int) ($n / 100)).' ratus '.self::words($n % 100));
        }
        if ($n < 2000) {
            return trim('seribu '.self::words($n - 1000));
        }
        if ($n < 1_000_000) {
            return trim(self::words((int) ($n / 1000)).' ribu '.self::words($n % 1000));
        }
        if ($n < 1_000_000_000) {
            return trim(self::words((int) ($n / 1_000_000)).' juta '.self::words($n % 1_000_000));
        }

        return trim(self::words((int) ($n / 1_000_000_000)).' miliar '.self::words($n % 1_000_000_000));
    }
}
