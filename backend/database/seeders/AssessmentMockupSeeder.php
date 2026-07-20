<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCriteria;
use App\Models\Organization;
use App\Models\Question;
use LogicException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AssessmentMockupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itemSA1 = $this->createSelfAssessmentExample('2026-Q1');
        $itemODA1 = $this->createOnDeskAssessmentExample($itemSA1);
        $itemOSA1 = $this->createOnSiteAssessmentExample($itemODA1);

        $itemSA2 = $this->createSelfAssessmentExample('2026-Q2');
        $this->createOnDeskAssessmentExample($itemSA2);

        $this->createSelfAssessmentExample('2026-Q3');
    }

    private function evidencePath(): string
    {
        static $evidencePath = '';
        if (empty($evidencePath)) {
            $evidencePath = 'images/placeholder.jpg';
            if (!Storage::disk('public')->exists($evidencePath)) {
                Storage::disk('public')->put(
                    $evidencePath,
                    file_get_contents( public_path('images/placeholder.jpg') )
                );
            }
        }

        return $evidencePath;
    }

    private function organizationId(): int
    {
        static $orgId = null;
        if (!$orgId) {
            $orgId = Organization::where('sname', 'mksr')
                ->firstOrFail()
                ->organization_id;
        }
        return $orgId;
    }

    private function createSelfAssessmentExample(string $period): Assessment
    {
        $evidencePath = $this->evidencePath();
        $orgId = $this->organizationId();

        static $questions = null;
        if (!$questions) $questions = Question::all();
        if ($questions->isEmpty()) {
            throw new LogicException('no active questions found to be used');
        }

        $assessment = Assessment::create([
            'organization_id' => $orgId,
            'period' => $period,
            'type' => Assessment::TYPE_SA,
            'status' => Assessment::STATUS_OPEN,
            'is_active' => false,
            'total_score' => null,
        ]);

        $answersData = [];
        $questions->each(function (Question $question) use ($assessment, &$answersData) {
            $answersData[] = [
                'assessment_id' => $assessment->id,
                'question_id' => $question->id,
            ];
        });
        AssessmentAnswer::fillAndInsert($answersData);

        $criteriasData = [];
        $totalScore = 0;
        $assessment->answers()
            ->with('question.criterias')
            ->lazy()
            ->each(function (AssessmentAnswer $answer)
                use ($evidencePath, &$criteriasData, &$totalScore)
            {
                $qCriteriaIds = $answer->question->criterias->modelKeys();
                foreach ($qCriteriaIds as $qCriteriaId) {
                    $value = $qCriteriaId % 3 === 1;
                    if ($value) $totalScore++;
                    $criteriasData[] = [
                        'assessment_answer_id' => $answer->id,
                        'question_criteria_id' => $qCriteriaId,
                        'value' => $value,
                        'evidence_path' => $value ? $evidencePath : null,
                    ];
                }
            });
        AssessmentCriteria::fillAndInsert($criteriasData);

        $assessment->status = Assessment::STATUS_SUBMITTED;
        $assessment->is_active = true;
        $assessment->total_score = $totalScore;
        $assessment->save();

        $assessment->refresh()->load('answers.criterias');
        return $assessment;
    }

    private function createOnDeskAssessmentExample(Assessment $src): Assessment
    {
        $evidencePath = $this->evidencePath();
        if ($src->type != Assessment::TYPE_SA) {
            throw new LogicException('arg assessment should be \'SA\' type');
        }

        $assessment = Assessment::create([
            'organization_id' => $src->organization_id,
            'period' => $src->period,
            'type' => Assessment::TYPE_ODA,
            'status' => Assessment::STATUS_OPEN,
            'is_active' => false,
            'total_score' => null,
            'prev_assessment_id' => $src->id,
        ]);

        $answersData = [];
        $prevCriteriasMap = [];
        $src->answers->each(function (AssessmentAnswer $answer) use ($assessment, &$answersData, &$prevCriteriasMap) {
            $answersData[] = [
                'assessment_id' => $assessment->id,
                'question_id' => $answer->question->id,
            ];

            foreach ($answer->criterias->toArray() as $criteria) {
                $prevCriteriasMap[$criteria['question_criteria_id']] = $criteria['value'];
            }
        });
        AssessmentAnswer::fillAndInsert($answersData);

        $criteriasData = [];
        $totalScore = 0;
        $assessment->answers()
            ->with('question.criterias')
            ->lazy()
            ->each(function (AssessmentAnswer $answer)
                use ($evidencePath, $prevCriteriasMap, &$criteriasData, &$totalScore)
            {
                $qCriteriaIds = $answer->question->criterias->modelKeys();
                foreach ($qCriteriaIds as $qCriteriaId) {
                    $value = ($prevCriteriasMap[$qCriteriaId] ?? false) || $qCriteriaId % 3 === 2;
                    if ($value) $totalScore++;
                    $criteriasData[] = [
                        'assessment_answer_id' => $answer->id,
                        'question_criteria_id' => $qCriteriaId,
                        'value' => $value,
                        'evidence_path' => $value ? $evidencePath : null,
                    ];
                }
            });
        AssessmentCriteria::fillAndInsert($criteriasData);

        $assessment->status = Assessment::STATUS_SUBMITTED;
        $assessment->is_active = true;
        $assessment->total_score = $totalScore;
        $assessment->save();

        $src->update([ 'is_active' => false ]);
        $assessment->refresh()->load('answers.criterias');
        return $assessment;
    }

    private function createOnSiteAssessmentExample(Assessment $src): Assessment
    {
        $evidencePath = $this->evidencePath();
        if ($src->type != Assessment::TYPE_ODA) {
            throw new LogicException('arg assessment should be \'ODA\' type');
        }

        $assessment = Assessment::create([
            'organization_id' => $src->organization_id,
            'period' => $src->period,
            'type' => Assessment::TYPE_OSA,
            'status' => Assessment::STATUS_OPEN,
            'is_active' => false,
            'total_score' => null,
            'prev_assessment_id' => $src->id,
        ]);

        $answersData = [];
        $prevCriteriasMap = [];
        $src->answers->each(function (AssessmentAnswer $answer) use ($assessment, &$answersData, &$prevCriteriasMap) {
            $answersData[] = [
                'assessment_id' => $assessment->id,
                'question_id' => $answer->question->id,
            ];

            foreach ($answer->criterias->toArray() as $criteria) {
                $prevCriteriasMap[$criteria['question_criteria_id']] = $criteria['value'];
            }
        });
        AssessmentAnswer::fillAndInsert($answersData);

        $criteriasData = [];
        $totalScore = 0;
        $assessment->answers()
            ->with('question.criterias')
            ->lazy()
            ->each(function (AssessmentAnswer $answer)
                use ($evidencePath, $prevCriteriasMap, &$criteriasData, &$totalScore)
            {
                $qCriteriaIds = $answer->question->criterias->modelKeys();
                foreach ($qCriteriaIds as $qCriteriaId) {
                    $value = ($prevCriteriasMap[$qCriteriaId] ?? false) || $qCriteriaId % 3 === 0;
                    if ($value) $totalScore++;
                    $criteriasData[] = [
                        'assessment_answer_id' => $answer->id,
                        'question_criteria_id' => $qCriteriaId,
                        'value' => $value,
                        'evidence_path' => $value ? $evidencePath : null,
                    ];
                }
            });
        AssessmentCriteria::fillAndInsert($criteriasData);

        $assessment->status = Assessment::STATUS_SUBMITTED;
        $assessment->is_active = true;
        $assessment->total_score = $totalScore;
        $assessment->save();

        $src->update([ 'is_active' => false ]);
        $assessment->refresh()->load('answers.criterias');
        return $assessment;
    }
}
