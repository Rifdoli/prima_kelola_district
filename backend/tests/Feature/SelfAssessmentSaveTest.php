<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AssessmentSelfController;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCriteria;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SelfAssessmentSaveTest extends TestCase
{
    use RefreshDatabase;

    private Assessment $assessment;
    private array $answers;

    protected function setUp(): void
    {
        parent::setUp();

        $orgTypeId = DB::table('organization_types')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'District',
            'level' => 1,
        ], 'organization_type_id');
        $orgId = DB::table('organizations')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'District Uji',
            'sname' => 'DU',
            'organization_type_id' => $orgTypeId,
            'is_active' => true,
        ], 'organization_id');

        $domain = QuestionGroup::create(['type' => 'domain', 'name' => 'D', 'weight' => 1]);
        $area = QuestionGroup::create(['type' => 'practice_area', 'name' => 'PA', 'weight' => 1, 'parent_id' => $domain->id]);
        $question = Question::create([
            'practice_area_id' => $area->id,
            'question' => 'Soal uji',
            'max_score' => 2,
            'sort_order' => 1,
        ]);
        $criterias = $question->criterias()->createMany([
            ['code' => 'A', 'sort_order' => 1, 'title' => 'A'],
            ['code' => 'B', 'sort_order' => 2, 'title' => 'B'],
        ]);

        $this->assessment = Assessment::create([
            'organization_id' => $orgId,
            'period' => '2026-Q3',
            'type' => Assessment::TYPE_SA,
            'status' => Assessment::STATUS_OPEN,
            'is_active' => false,
            'total_score' => null,
        ]);

        $this->answers = [
            $criterias[0]->id => ['criteria_id' => $criterias[0]->id, 'value' => true, 'evidence_path' => null, 'note' => null],
            $criterias[1]->id => ['criteria_id' => $criterias[1]->id, 'value' => false, 'evidence_path' => null, 'note' => null],
        ];
    }

    private function save(bool $storeAsDraft): void
    {
        $method = new \ReflectionMethod(AssessmentSelfController::class, 'saveAssessmentAnswers');
        $method->setAccessible(true);
        $method->invoke(new AssessmentSelfController, $this->assessment->fresh('answers'), $this->answers, $storeAsDraft);
    }

    public function test_menyimpan_draft_berulang_tidak_menggandakan_jawaban(): void
    {
        $this->save(storeAsDraft: true);
        $this->save(storeAsDraft: true);
        $this->save(storeAsDraft: true);

        $this->assertSame(1, AssessmentAnswer::where('assessment_id', $this->assessment->id)->count());
        $this->assertSame(2, AssessmentCriteria::count());
        $this->assertEquals(50.0, $this->assessment->fresh()->total_score);
        $this->assertNotNull($this->assessment->fresh()->category);
    }

    public function test_submit_menyalin_jawaban_ke_assessment_oda(): void
    {
        $this->save(storeAsDraft: false);

        $oda = Assessment::where('type', Assessment::TYPE_ODA)->firstOrFail();
        $odaAnswerIds = AssessmentAnswer::where('assessment_id', $oda->id)->pluck('id');

        $this->assertCount(1, $odaAnswerIds);
        // kriteria ODA harus menempel ke answer milik ODA, bukan menumpuk di answer SA
        $this->assertSame(2, AssessmentCriteria::whereIn('assessment_answer_id', $odaAnswerIds)->count());
        $this->assertSame(2, AssessmentCriteria::whereIn(
            'assessment_answer_id',
            AssessmentAnswer::where('assessment_id', $this->assessment->id)->pluck('id')
        )->count());
    }
}
