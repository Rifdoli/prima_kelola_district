<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\QuestionController;
use App\Http\Requests\Question\UpdateQuestionCriteriasRequest;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCriteria;
use App\Models\Question;
use App\Models\QuestionCriteria;
use App\Models\QuestionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class RubricLockTest extends TestCase
{
    use RefreshDatabase;

    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $domain = QuestionGroup::create(['type' => 'domain', 'name' => 'D', 'weight' => 1]);
        $area = QuestionGroup::create(['type' => 'practice_area', 'name' => 'PA', 'weight' => 1, 'parent_id' => $domain->id]);
        $this->question = Question::create([
            'practice_area_id' => $area->id,
            'question' => 'Soal uji',
            'max_score' => 2,
            'sort_order' => 1,
        ]);
        $this->question->criterias()->createMany([
            ['code' => 'A', 'sort_order' => 1, 'title' => 'A'],
            ['code' => 'B', 'sort_order' => 2, 'title' => 'B'],
        ]);
    }

    private function answerWith(string $status): AssessmentCriteria
    {
        $orgTypeId = DB::table('organization_types')->insertGetId(
            ['uuid' => (string) Str::uuid(), 'name' => 'District', 'level' => 1],
            'organization_type_id'
        );
        $orgId = DB::table('organizations')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'District Uji',
            'sname' => 'DU'.Str::random(4),
            'organization_type_id' => $orgTypeId,
            'is_active' => true,
        ], 'organization_id');

        $assessment = Assessment::create([
            'organization_id' => $orgId,
            'period' => '2026-Q3',
            'type' => Assessment::TYPE_SA,
            'status' => $status,
            'is_active' => false,
            'total_score' => 1,
        ]);
        $answer = AssessmentAnswer::create([
            'assessment_id' => $assessment->id,
            'question_id' => $this->question->id,
        ]);

        return AssessmentCriteria::create([
            'assessment_answer_id' => $answer->id,
            'question_criteria_id' => $this->question->criterias()->first()->id,
            'value' => true,
        ]);
    }

    private function updateCriterias(): int
    {
        $request = UpdateQuestionCriteriasRequest::create('/x', 'PATCH', [
            'criterias' => [['title' => 'naskah baru']],
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        return (new QuestionController)
            ->updateCriterias($request, $this->question->id)
            ->getStatusCode();
    }

    public function test_kriteria_terkunci_setelah_assessment_disubmit(): void
    {
        $this->answerWith(Assessment::STATUS_SUBMITTED);

        $this->assertSame(409, $this->updateCriterias());
        $this->assertSame(2, $this->question->criterias()->count());
        $this->assertSame('A', $this->question->criterias()->first()->title);
    }

    public function test_kriteria_masih_bisa_diubah_saat_assessment_belum_disubmit(): void
    {
        $this->answerWith(Assessment::STATUS_DRAFT);

        $this->assertSame(200, $this->updateCriterias());
        $this->assertSame(1, $this->question->criterias()->count());
    }

    public function test_kriteria_terarsip_tetap_terbaca_dari_sisi_jawaban(): void
    {
        $assCriteria = $this->answerWith(Assessment::STATUS_SUBMITTED);
        QuestionCriteria::whereKey($assCriteria->question_criteria_id)->delete();

        $this->assertSame('A', $assCriteria->fresh()->questionCriteria->title);
    }
}
