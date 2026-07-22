<?php

namespace Tests\Unit;

use App\Support\AssessmentScore;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AssessmentScoreTest extends TestCase
{
    private function item(string $domain, string $pa, float $wd, float $wpa, int $achieved, int $max): array
    {
        return [
            'domain' => $domain, 'practice_area' => $pa,
            'weight_domain' => $wd, 'weight_pa' => $wpa,
            'achieved' => $achieved, 'max' => $max,
        ];
    }

    public function test_skor_sempurna_seratus(): void
    {
        $items = [$this->item('D', 'PA', 1.0, 1.0, 5, 5)];
        $this->assertSame(100.0, AssessmentScore::weightedTotal($items));
    }

    /** Dua soal berbagi 1 bobot PA: satu penuh, satu kosong → rata-rata 0.5, bukan bocor ke 404. */
    public function test_rata_rata_dalam_practice_area(): void
    {
        $items = [
            $this->item('D', 'PA', 1.0, 1.0, 5, 5),
            $this->item('D', 'PA', 1.0, 1.0, 0, 5),
        ];
        $this->assertSame(50.0, AssessmentScore::weightedTotal($items));
    }

    public function test_bobot_antar_domain(): void
    {
        $full = [
            $this->item('A', 'PA1', 0.3, 1.0, 5, 5),
            $this->item('B', 'PA2', 0.7, 1.0, 5, 5),
        ];
        $this->assertSame(100.0, AssessmentScore::weightedTotal($full));

        $bBesarKosong = [
            $this->item('A', 'PA1', 0.3, 1.0, 5, 5),
            $this->item('B', 'PA2', 0.7, 1.0, 0, 5),
        ];
        $this->assertSame(30.0, AssessmentScore::weightedTotal($bBesarKosong));
    }

    /** Soal tak dijawab (achieved=0) tetap masuk penyebut, bukan diabaikan. */
    public function test_soal_tak_dijawab_menekan_skor(): void
    {
        $items = [
            $this->item('D', 'PA', 0.5, 1.0, 4, 4), // domain lain penuh → 50
            $this->item('D', 'PA', 0.5, 1.0, 0, 3), // 0 tapi tetap dihitung
        ];
        // PA sama, rata-rata (1 + 0)/2 = 0.5, × 0.5 × 1 × 100 = 25.0
        $this->assertSame(25.0, AssessmentScore::weightedTotal($items));
    }

    #[DataProvider('bandProvider')]
    public function test_band_kategori(?float $score, ?string $expected): void
    {
        $this->assertSame($expected, AssessmentScore::category($score));
    }

    public static function bandProvider(): array
    {
        return [
            [29.99, 'DASAR'],
            [30.0, 'BERKEMBANG'],
            [49.99, 'BERKEMBANG'],
            [50.0, 'TERKELOLA'],
            [69.99, 'TERKELOLA'],
            [70.0, 'TERUKUR'],
            [89.99, 'TERUKUR'],
            [90.0, 'PRIMA'],
            [100.0, 'PRIMA'],
            [null, null],
        ];
    }
}
