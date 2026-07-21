<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\QuestionController;
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

class QuestionForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function forceDelete(array $ids): int
    {
        $method = new \ReflectionMethod(QuestionController::class, 'forceDeleteQuestions');
        $method->setAccessible(true);

        return $method->invoke(new QuestionController, $ids);
    }

    private function makeQuestion(): Question
    {
        $domain = QuestionGroup::create(['type' => 'domain', 'name' => 'D'.Str::random(4), 'weight' => 1]);
        $area = QuestionGroup::create(['type' => 'practice_area', 'name' => 'PA'.Str::random(4), 'weight' => 1, 'parent_id' => $domain->id]);
        $question = Question::create([
            'practice_area_id' => $area->id,
            'question' => 'Soal uji',
            'max_score' => 2,
            'sort_order' => 1,
        ]);
        $question->criterias()->createMany([
            ['code' => 'A', 'sort_order' => 1, 'title' => 'A'],
            ['code' => 'B', 'sort_order' => 2, 'title' => 'B'],
        ]);

        return $question;
    }

    public function test_force_delete_ikut_membuang_kriteria_termasuk_yang_terarsip(): void
    {
        $question = $this->makeQuestion();
        $question->criterias()->first()->delete(); // satu kriteria diarsipkan dulu

        $this->assertSame(1, $this->forceDelete([$question->id]));

        $this->assertNull(Question::withTrashed()->find($question->id));
        $this->assertSame(0, QuestionCriteria::withTrashed()->where('question_id', $question->id)->count());
    }

    public function test_force_delete_ditolak_saat_kriteria_dipakai_assessment(): void
    {
        $question = $this->makeQuestion();
        $criteria = $question->criterias()->first();

        $orgTypeId = DB::table('organization_types')->insertGetId(
            ['uuid' => (string) Str::uuid(), 'name' => 'District', 'level' => 1],
            'organization_type_id'
        );
        $orgId = DB::table('organizations')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'District Uji',
            'sname' => 'DU',
            'organization_type_id' => $orgTypeId,
            'is_active' => true,
        ], 'organization_id');

        $assessment = Assessment::create([
            'organization_id' => $orgId,
            'period' => '2026-Q3',
            'type' => Assessment::TYPE_SA,
            'status' => Assessment::STATUS_SUBMITTED,
            'is_active' => false,
            'total_score' => 1,
        ]);
        $answer = AssessmentAnswer::create(['assessment_id' => $assessment->id, 'question_id' => $question->id]);
        AssessmentCriteria::create([
            'assessment_answer_id' => $answer->id,
            'question_criteria_id' => $criteria->id,
            'value' => true,
        ]);

        $deleted = $this->forceDelete([$question->id]);

        $this->assertSame(0, $deleted);
        $this->assertNotNull(Question::withTrashed()->find($question->id));
        $this->assertSame(2, QuestionCriteria::withTrashed()->where('question_id', $question->id)->count());
    }
}
