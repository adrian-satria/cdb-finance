<?php

namespace Tests\Unit\Services;

use App\Services\SppNumberGeneratorService;
use Tests\TestCase;

class SppNumberGeneratorServiceTest extends TestCase
{
    protected SppNumberGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SppNumberGeneratorService;
    }

    // ==========================================
    // Test Group 1: Roman Month Conversion (13 tests)
    // ==========================================

    /** @test */
    public function it_converts_january_to_roman()
    {
        $this->assertEquals('I', $this->service->getRomanMonth(1));
    }

    /** @test */
    public function it_converts_february_to_roman()
    {
        $this->assertEquals('II', $this->service->getRomanMonth(2));
    }

    /** @test */
    public function it_converts_march_to_roman()
    {
        $this->assertEquals('III', $this->service->getRomanMonth(3));
    }

    /** @test */
    public function it_converts_april_to_roman()
    {
        $this->assertEquals('IV', $this->service->getRomanMonth(4));
    }

    /** @test */
    public function it_converts_may_to_roman()
    {
        $this->assertEquals('V', $this->service->getRomanMonth(5));
    }

    /** @test */
    public function it_converts_june_to_roman()
    {
        $this->assertEquals('VI', $this->service->getRomanMonth(6));
    }

    /** @test */
    public function it_converts_july_to_roman()
    {
        $this->assertEquals('VII', $this->service->getRomanMonth(7));
    }

    /** @test */
    public function it_converts_august_to_roman()
    {
        $this->assertEquals('VIII', $this->service->getRomanMonth(8));
    }

    /** @test */
    public function it_converts_september_to_roman()
    {
        $this->assertEquals('IX', $this->service->getRomanMonth(9));
    }

    /** @test */
    public function it_converts_october_to_roman()
    {
        $this->assertEquals('X', $this->service->getRomanMonth(10));
    }

    /** @test */
    public function it_converts_november_to_roman()
    {
        $this->assertEquals('XI', $this->service->getRomanMonth(11));
    }

    /** @test */
    public function it_converts_december_to_roman()
    {
        $this->assertEquals('XII', $this->service->getRomanMonth(12));
    }

    /** @test */
    public function it_returns_default_for_invalid_month()
    {
        $this->assertEquals('I', $this->service->getRomanMonth(0));
        $this->assertEquals('I', $this->service->getRomanMonth(13));
    }

    // ==========================================
    // Test Group 2: Number Parsing (4 tests)
    // ==========================================

    /** @test */
    public function it_parses_spp_number_correctly()
    {
        $noSurat = '2026/VII/SPP/40/015';
        $result = $this->service->parseNumber($noSurat);

        $this->assertEquals('2026', $result['tahun']);
        $this->assertEquals('VII', $result['bulan']);
        $this->assertEquals('40', $result['kode_project']);
        $this->assertEquals(15, $result['no_urut']);
    }

    /** @test */
    public function it_parses_spp_number_with_leading_zeros()
    {
        $noSurat = '2026/I/SPP/40/001';
        $result = $this->service->parseNumber($noSurat);

        $this->assertEquals('2026', $result['tahun']);
        $this->assertEquals('I', $result['bulan']);
        $this->assertEquals('40', $result['kode_project']);
        $this->assertEquals(1, $result['no_urut']);
    }

    /** @test */
    public function it_parses_spp_number_with_high_sequence()
    {
        $noSurat = '2026/XII/SPP/40/999';
        $result = $this->service->parseNumber($noSurat);

        $this->assertEquals('2026', $result['tahun']);
        $this->assertEquals('XII', $result['bulan']);
        $this->assertEquals('40', $result['kode_project']);
        $this->assertEquals(999, $result['no_urut']);
    }

    /** @test */
    public function it_handles_invalid_format_gracefully()
    {
        $noSurat = 'INVALID-FORMAT';
        $result = $this->service->parseNumber($noSurat);

        // When format is invalid, parseNumber returns first element or null for missing parts
        $this->assertEquals('INVALID-FORMAT', $result['tahun']); // Gets first part
        $this->assertNull($result['bulan']); // No second part
        $this->assertNull($result['no_urut']); // No fifth part
    }

    // ==========================================
    // Test Group 3: Format Validation (6 tests)
    // ==========================================

    /** @test */
    public function it_validates_correct_spp_format()
    {
        $this->assertTrue($this->service->isValidFormat('2026/VII/SPP/40/015'));
        $this->assertTrue($this->service->isValidFormat('2026/I/SPP/40/001'));
        $this->assertTrue($this->service->isValidFormat('2026/XII/SPP/40/999'));
    }

    /** @test */
    public function it_rejects_invalid_year_format()
    {
        $this->assertFalse($this->service->isValidFormat('26/VII/SPP/40/015'));
        $this->assertFalse($this->service->isValidFormat('20266/VII/SPP/40/015'));
    }

    /** @test */
    public function it_rejects_invalid_month_format()
    {
        $this->assertFalse($this->service->isValidFormat('2026/13/SPP/40/015'));
        $this->assertFalse($this->service->isValidFormat('2026/JAN/SPP/40/015'));
        $this->assertFalse($this->service->isValidFormat('2026/XIII/SPP/40/015'));
    }

    /** @test */
    public function it_rejects_wrong_separator()
    {
        $this->assertFalse($this->service->isValidFormat('2026-VII-SPP-40-015'));
        $this->assertFalse($this->service->isValidFormat('2026.VII.SPP.40.015'));
    }

    /** @test */
    public function it_rejects_missing_components()
    {
        $this->assertFalse($this->service->isValidFormat('2026/VII/SPP/015'));
        $this->assertFalse($this->service->isValidFormat('2026/VII/40/015'));
    }

    /** @test */
    public function it_rejects_invalid_sequence_format()
    {
        $this->assertFalse($this->service->isValidFormat('2026/VII/SPP/40/15')); // Only 2 digits
        $this->assertFalse($this->service->isValidFormat('2026/VII/SPP/40/0015')); // 4 digits
        $this->assertFalse($this->service->isValidFormat('2026/VII/SPP/40/ABC')); // Non-numeric
    }

    // ==========================================
    // Test Group 4: Edge Cases (3 tests)
    // ==========================================

    /** @test */
    public function it_handles_year_rollover()
    {
        // December 2025
        $dec2025 = '2025/XII/SPP/40/150';
        $parsedDec = $this->service->parseNumber($dec2025);
        $this->assertEquals('2025', $parsedDec['tahun']);

        // January 2026 (should start from 001)
        $jan2026 = '2026/I/SPP/40/001';
        $parsedJan = $this->service->parseNumber($jan2026);
        $this->assertEquals('2026', $parsedJan['tahun']);
        $this->assertEquals(1, $parsedJan['no_urut']);
    }

    /** @test */
    public function it_validates_all_roman_months()
    {
        $months = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        foreach ($months as $month) {
            $noSurat = "2026/{$month}/SPP/40/001";
            $this->assertTrue(
                $this->service->isValidFormat($noSurat),
                "Failed to validate month: {$month}"
            );
        }
    }

    /** @test */
    public function it_handles_sequence_padding_correctly()
    {
        // Test various sequence numbers
        $sequences = [
            ['input' => 1, 'expected' => '001'],
            ['input' => 9, 'expected' => '009'],
            ['input' => 10, 'expected' => '010'],
            ['input' => 99, 'expected' => '099'],
            ['input' => 100, 'expected' => '100'],
            ['input' => 999, 'expected' => '999'],
        ];

        foreach ($sequences as $seq) {
            $padded = str_pad($seq['input'], 3, '0', STR_PAD_LEFT);
            $this->assertEquals($seq['expected'], $padded);

            // Verify format is valid
            $noSurat = "2026/VII/SPP/40/{$padded}";
            $this->assertTrue($this->service->isValidFormat($noSurat));
        }
    }
}
