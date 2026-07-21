<?php

namespace Tests\Unit;

use App\Http\Requests\Question\UpdateQuestionCriteriasRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateQuestionCriteriasRequestTest extends TestCase
{
    private function validate(array $payload): bool
    {
        return Validator::make($payload, (new UpdateQuestionCriteriasRequest)->rules())->passes();
    }

    /** Tanpa guard ini, payload kosong lolos validasi & updateCriterias menghapus semua kriteria. */
    public function test_criterias_wajib_ada_dan_tidak_boleh_kosong(): void
    {
        $this->assertFalse($this->validate([]));
        $this->assertFalse($this->validate(['criterias' => []]));
        $this->assertTrue($this->validate([
            'criterias' => [['id' => 1, 'title' => 'Kriteria A']],
        ]));
    }

    public function test_reference_dan_evidence_hint_diterima(): void
    {
        $this->assertTrue($this->validate([
            'criterias' => [[
                'title' => 'Kriteria A',
                'reference' => 'ISO 55001 A.5.1',
                'evidence_hint' => null,
            ]],
        ]));
    }

    /** Kolom title bertipe text; data rubrik ada yang 315 karakter. */
    public function test_title_panjang_diterima(): void
    {
        $this->assertTrue($this->validate([
            'criterias' => [['title' => str_repeat('a', 315)]],
        ]));
    }
}
