<?php

namespace App\Support;

/**
 * Rumus skor berbobot rubrik Prima District. Table-agnostic: hanya menerima
 * data primitif, tidak mengenal tabel/model apa pun. Ini satu-satunya tempat
 * formula & band kategori hidup — pemanggil (controller) yang memetakan datanya.
 */
class AssessmentScore
{
    /**
     * @param  iterable<array{domain:string, practice_area:string, weight_domain:float,
     *                        weight_pa:float, achieved:int, max:int}>  $items
     *         WAJIB berisi SEMUA soal (yang tak dijawab: achieved = 0), supaya
     *         skor tidak menggelembung. Skor sempurna = 100.0.
     */
    public static function weightedTotal(iterable $items): float
    {
        // Kelompokkan per practice area (domain + practice_area).
        $groups = [];
        foreach ($items as $it) {
            $groups[$it['domain'].'|'.$it['practice_area']][] = $it;
        }

        $total = 0.0;
        foreach ($groups as $group) {
            $ratios = [];
            $weightDomain = $weightPa = null;

            foreach ($group as $it) {
                $max = (int) $it['max'];
                if ($max <= 0) {
                    continue;
                }
                $ratios[] = min(1.0, $it['achieved'] / $max); // 0..1
                $weightDomain = (float) $it['weight_domain'];
                $weightPa = (float) $it['weight_pa'];
            }

            if (! $ratios || $weightDomain === null) {
                continue; // bobot belum di-seed → lewati (harusnya tak terjadi)
            }

            $paRatio = array_sum($ratios) / count($ratios); // RATA-RATA di dalam PA
            $total += $paRatio * $weightPa * $weightDomain * 100;
        }

        return round($total, 2);
    }

    public static function category(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        return match (true) {
            $score < 30 => 'DASAR',
            $score < 50 => 'BERKEMBANG',
            $score < 70 => 'TERKELOLA',
            $score < 90 => 'TERUKUR',
            default => 'PRIMA',
        };
    }
}
