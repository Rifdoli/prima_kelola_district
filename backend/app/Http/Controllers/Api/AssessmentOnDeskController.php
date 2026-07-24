<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationMapping;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCriteria;
use App\Models\Question;
use App\Models\QuestionCriteria;
use App\Models\QuestionGroup;
use App\Http\Requests\OnDeskAssessment\GetAssessmentRequest;
use App\Http\Requests\OnDeskAssessment\StoreAssessmentAnswersRequest;
use App\Http\Requests\OnDeskAssessment\StoreAssessmentEvidenceRequest;
use App\Services\Evidence\AssessmentEvidence;
use App\Traits\ApiResponse;
use App\Rules\AssessmentPeriod;
use App\Support\AssessmentScore;
use App\Exceptions\Assessment\AssessmentLockedException;
use App\Exceptions\Assessment\InvalidQCriteriaException;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AssessmentOnDeskController extends Controller
{
    use ApiResponse;

    public function index(GetAssessmentRequest $request, string $period, int $orgId)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $params = $request->validated();

        if (!in_array($orgId, $this->getUserOrganizationScope())) {
            return $this->error('Forbidden', 403);
        }

        // DB::listen(function ($query) use (&$count) {
        //     $count++;
        //     dump("#{$count} in {$query->time}ms:" . $query->toRawSql());
        // });

        $assessment = Assessment::where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_ODA)
            ->firstOrFail();
        $domains = [];
        $practiceAreas = [];
        $questions = [];
        $criterias = [];
        $answers = [];

        $tableACs = with(new AssessmentCriteria)->getTable();
        $tableAAs = with(new AssessmentAnswer)->getTable();
        $tableQs = with(new Question)->getTable();
        $tableQCs = with(new QuestionCriteria)->getTable();
        $assCriteriasQuery = AssessmentCriteria::with('questionCriteria.question.practiceArea.domain')
            ->select("$tableACs.*")
            ->join($tableQCs, "$tableQCs.id", "$tableACs.question_criteria_id")
            ->join($tableQs, "$tableQs.id", "$tableQCs.question_id")
            ->join($tableAAs, "$tableAAs.id", "$tableACs.assessment_answer_id")
            ->where("$tableAAs.assessment_id", $assessment->id)
            ->orderBy("$tableQs.sort_order")
            ->orderBy("$tableQCs.sort_order");

        if (!empty($params['domain_ids'])) {
            $tableQGs = with(new QuestionGroup)->getTable();
            $assCriteriasQuery->join($tableQGs, "$tableQGs.id", "$tableQs.practice_area_id")
                ->whereIn("$tableQGs.parent_id", $params['domain_ids']);
        }

        if (!empty($params['practice_area_ids'])) {
            $assCriteriasQuery->whereIn("$tableQs.practice_area_id", $params['practice_area_ids']);
        }

        if (!empty($params['question_ids'])) {
            $assCriteriasQuery->whereIn("$tableQs.id", $params['question_ids']);
        }

        foreach ($assCriteriasQuery->lazy() as $assCriteria) {
            $domain = $assCriteria->questionCriteria->question->practiceArea->domain;
            if (!isset($domains[$domain->id])) {
                $domains[$domain->id] = $domain->only(['id', 'name', 'weight']);
            }

            $practiceArea = $assCriteria->questionCriteria->question->practiceArea;
            if (!isset($practiceAreas[$practiceArea->id])) {
                $practiceAreas[$practiceArea->id] = [
                    ...$practiceArea->only(['id', 'name', 'weight']),
                    'domain_id' => $domain->id,
                ];
            }

            $question = $assCriteria->questionCriteria->question;
            if (!isset($questions[$question->id])) {
                $questions[$question->id] = [
                    ...$question->only([
                        'id', 'question', 'scope', 'perangkat',
                        'max_score', 'sort_order'
                    ]),
                    'practice_area_id' => $practiceArea->id,
                    'domain_id' => $domain->id,
                ];
            }

            $criterias[] = [
                ...$assCriteria->questionCriteria->only([
                    'id', 'code', 'sort_order', 'title',
                    'reference', 'evidence_hint'
                ]),
                'question_id' => $question->id,
                'practice_area_id' => $practiceArea->id,
                'domain_id' => $domain->id,
            ];

            $answer = [
                'id' => $assCriteria->id,
                'value' => $assCriteria->value,
                'evidence_path' => $assCriteria->evidence_path,
                'evidence_url' => null,
                'note' => $assCriteria->note,
                'criteria_id' => $assCriteria->question_criteria_id,
                'question_id' => $question->id,
                'practice_area_id' => $practiceArea->id,
                'domain_id' => $domain->id,
            ];

            if ($assCriteria->evidence_path) {
                $answer['evidence_url'] = Storage::disk('public')->url($assCriteria->evidence_path);
            }
            $answers[] = $answer;
        }

        $message = 'On Desk Assessment siap diisi.';
        if ($assessment->status == Assessment::STATUS_SUBMITTED) {
            $message = 'On Desk Assessment telah diisi.';
        }

        return $this->success(
            data: [
                'assessment' => $assessment,
                'domains' => array_values($domains),
                'practice_areas' => array_values($practiceAreas),
                'questions' => array_values($questions),
                'criterias' => $criterias,
                'answers' => $answers,
            ],
            message: $message,
        );
    }

    public function store(StoreAssessmentAnswersRequest $request, string $period, int $orgId)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $newCriterias = array_column($request->validated('answers'), null, 'criteria_id');

        if (!in_array($orgId, $this->getUserOrganizationScope())) {
            return $this->error('Forbidden', 403);
        }

        $assessment = Assessment::where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_ODA)
            ->firstOrFail();

        try {
            $this->saveAssessmentAnswers(
                assessment: $assessment,
                newCriterias: $newCriterias,
                storeAsDraft: false
            );
        } catch (AssessmentLockedException $e) {
            return $this->error(
                message: "Status On Desk Assessment sudah dikunci dan tidak dapat di-submit.",
                status: 409
            );
        } catch (InvalidQCriteriaException $e) {
            $message = 'Kriteria Pertanyaan tidak ditemukan';
            if ($criteriaId = $e->getCriteriaId()) {
                $message .= " (id:$criteriaId)";
            }
            $message .= '.';
            return $this->error(message: $message, status: 400);
        }

        return $this->success(
            message: 'On Desk Assessment berhasil di-submit.',
            status: 200
        );
    }

    public function storeDraft(StoreAssessmentAnswersRequest $request, string $period, int $orgId)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $newCriterias = array_column($request->validated('answers'), null, 'criteria_id');

        if (!in_array($orgId, $this->getUserOrganizationScope())) {
            return $this->error('Forbidden', 403);
        }

        $assessment = Assessment::where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_ODA)
            ->firstOrFail();

        try {
            $this->saveAssessmentAnswers(
                assessment: $assessment,
                newCriterias: $newCriterias,
                storeAsDraft: true
            );
        } catch (AssessmentLockedException $e) {
            return $this->error(
                message: "Status On Desk Assessment sudah dikunci dan tidak dapat di-submit.",
                status: 409
            );
        } catch (InvalidQCriteriaException $e) {
            $message = 'Kriteria Pertanyaan tidak ditemukan';
            if ($criteriaId = $e->getCriteriaId()) {
                $message .= " (id:$criteriaId)";
            }
            $message .= '.';
            return $this->error(message: $message, status: 400);
        }

        return $this->success(
            message: 'On Desk Assessment disimpan sebagai draft.',
            status: 200
        );
    }

    /**
     * AssessmentEvidence service membutuhkan queue worker untuk
     * menjalankan `CleanupUnusedEvidenceJob`.
     */
    public function storeEvidence(StoreAssessmentEvidenceRequest $request, string $period, int $orgId)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $body = $request->validated();

        $assessment = Assessment::where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_ODA)
            ->first();
        if (!$assessment) {
            return $this->error('Target Assessment tidak ditemukan.', 404);
        } elseif ($assessment->status === Assessment::STATUS_SUBMITTED) {
            return $this->error('Upload evidence telah ditutup untuk Assessment ini.', 403);
        }

        $year = (int) substr($period, 0, 4);
        $quarter = (int) substr($period, -1);
        $evidenceService = (new AssessmentEvidence)
            ->setUploadYear($year)
            ->setUploadQuarter($quarter);
        $evidence = $evidenceService->upload($body['file']);

        return $this->success(
            data: ['path' => $evidence->path, 'url' => $evidence->url],
            message: 'Evidence berhasil diupload.',
            status: 201
        );
    }

    /**
     * @return int[]
     * @todo Implement local static variable jika perlu digunakan berkali-kali
     */
    private function getUserOrganizationScope(): array
    {
        $tableMaps = with(new OrganizationMapping)->getTable();
        $tableOrgs = with(new Organization)->getTable();
        $orgIds = DB::table($tableMaps)
            ->join($tableOrgs, "$tableOrgs.organization_id", '=', "$tableMaps.descendant_id")
            ->select("$tableMaps.descendant_id")
            ->where("$tableMaps.is_active", true)
            ->where("$tableOrgs.is_active", true)
            ->where("$tableMaps.ancestor_id", auth()->user()->organization_id)
            ->pluck('descendant_id')
            ->all();
        return $orgIds;
    }

    private function saveAssessmentAnswers(
        Assessment $assessment,
        array $newCriterias,
        bool $storeAsDraft
    ): void {
        if ($assessment->status == Assessment::STATUS_SUBMITTED) {
            throw new AssessmentLockedException;
        }

        $assAnswersData = [];

        $tableACs = with(new AssessmentCriteria)->getTable();
        $tableAAs = with(new AssessmentAnswer)->getTable();
        $assCriteriasQuery = AssessmentCriteria::with('questionCriteria.question.practiceArea.domain')
            ->select("$tableACs.*")
            ->join($tableAAs, "$tableAAs.id", "$tableACs.assessment_answer_id")
            ->where("$tableAAs.assessment_id", $assessment->id);

        $matchedCriteriaIds = [];
        $updatedAssCriterias = [];
        $scoreItems = [];
        $osaCriteriasMap = [];
        foreach ($assCriteriasQuery->lazy() as $assCriteria) {
            $criteriaId = $assCriteria->question_criteria_id;
            $value = $assCriteria->value;
            if (isset($newCriterias[$criteriaId])) {

                $newCriteria = $newCriterias[$criteriaId];
                $value = filter_var($newCriteria['value'], FILTER_VALIDATE_BOOLEAN);
                $updatedAssCriterias[$assCriteria->id] = [
                    'assessment_answer_id' => $assCriteria->assessment_answer_id,
                    'question_criteria_id' => $criteriaId,
                    'value' => $value,
                    'evidence_path' => $newCriteria['evidence_path'],
                    'note' => $newCriteria['note'],
                ];

                $matchedCriteriaIds[$criteriaId] = $criteriaId;

            }

            $questionId = $assCriteria->questionCriteria->question_id;
            $question = $assCriteria->questionCriteria->question;
            if (!isset($scoreItems[$questionId])) {
                $scoreItems[$questionId] = [
                    'domain' => $question->practiceArea->domain->name,
                    'practice_area' => $question->practiceArea->name,
                    'weight_domain' => (float) $question->practiceArea->domain->weight,
                    'weight_pa' => (float) $question->practiceArea->weight,
                    'achieved' => 0,
                    'max' => 0,
                ];
            }

            $scoreItems[$questionId]['max']++;
            if ($value === true) {
                $scoreItems[$questionId]['achieved']++;
            }

            if ($storeAsDraft) continue;
            if (!isset($osaCriteriasMap[$questionId])) {
                $osaCriteriasMap[$questionId] = [];
            }

            $osaCriteriaMap = [
                'assessment_answer_id' => null,
                'question_criteria_id' => $assCriteria->question_criteria_id,
                'value' => $assCriteria->value,
                'evidence_path' => $assCriteria->evidence_path,
                'note' => $assCriteria->note,
            ];

            if (isset($updatedAssCriterias[$assCriteria->id])) {
                $osaCriteriaMap['value'] = $updatedAssCriterias[$assCriteria->id]['value'];
                $osaCriteriaMap['evidence_path'] = $updatedAssCriterias[$assCriteria->id]['evidence_path'];
                $osaCriteriaMap['note'] = $updatedAssCriterias[$assCriteria->id]['note'];
            }

            $osaCriteriasMap[$questionId][] = $osaCriteriaMap;
        }

        if (count($newCriterias) > count($matchedCriteriaIds)) {
            foreach (array_keys($newCriterias) as $criteriaId) {
                if (!isset($matchedCriteriaIds[$criteriaId])) {
                    throw (new InvalidQCriteriaException)->setCriteriaId($criteriaId);
                }
            }
        }

        if (empty($updatedAssCriterias)) {
            throw new \LogicException('no assessment criterias to update');
        }

        $totalScore = AssessmentScore::weightedTotal(array_values($scoreItems));
        unset($newCriterias, $matchedCriteriaIds, $scoreItems);
        DB::transaction(function () use ($assessment, $storeAsDraft, $updatedAssCriterias, $totalScore, $osaCriteriasMap) {
            $oldAssCriteriaIds = array_keys($updatedAssCriterias);
            AssessmentCriteria::whereIn('id', $oldAssCriteriaIds)->delete();
            AssessmentCriteria::fillAndInsert(array_values($updatedAssCriterias));
            unset($updatedAssCriterias);

            if ($storeAsDraft) {
                if ($assessment->status != Assessment::STATUS_DRAFT) {
                    $assessment->status = Assessment::STATUS_DRAFT;
                }

                if (!$assessment->is_active) {
                    $assessment->is_active = true;
                }

                $assessment->total_score = $totalScore;
                $assessment->save();
                return;
            }

            $assessment->status = Assessment::STATUS_SUBMITTED;
            $assessment->is_active = false;
            $assessment->total_score = $totalScore;
            $assessment->save();

            $newAssessment = Assessment::create([
                'organization_id' => $assessment->organization_id,
                'period' => $assessment->period,
                'type' => Assessment::TYPE_OSA,
                'status' => Assessment::STATUS_OPEN,
                'is_active' => true,
                'total_score' => $totalScore,
                'prev_assessment_id' => $assessment->id,
            ]);

            $assAnswersData = [];
            foreach (array_keys($osaCriteriasMap) as $questionId) {
                $assAnswersData[] = [
                    'assessment_id' => $newAssessment->id,
                    'question_id' => $questionId,
                ];
            }
            AssessmentAnswer::fillAndInsert($assAnswersData);
            $newAssAnswers = AssessmentAnswer::where('assessment_id', $newAssessment->id)->get();

            $assCriteriasData = [];
            foreach ($newAssAnswers as $assAnswer) {
                foreach ($osaCriteriasMap[$assAnswer->question_id] as $assCriteriaData) {
                    $assCriteriaData['assessment_answer_id'] = $assAnswer->id;
                    $assCriteriasData[] = $assCriteriaData;
                }
            }
            AssessmentCriteria::fillAndInsert($assCriteriasData);
        });
    }

    public function showExampleAnswersBody(string $period, int $orgId)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();

        if (!in_array($orgId, $this->getUserOrganizationScope())) {
            return $this->error('Forbidden', 403);
        }

        $assessment = Assessment::where('organization_id', $orgId)
            ->where('period', $period)
            ->whereIn('type', [Assessment::TYPE_ODA, Assessment::TYPE_SA])
            ->orderByRaw('prev_assessment_id IS NULL')
            ->orderByDesc('prev_assessment_id')
            ->firstOrFail();

        $tableACs = with(new AssessmentCriteria)->getTable();
        $tableAAs = with(new AssessmentAnswer)->getTable();
        $assCriteriasQuery = AssessmentCriteria::select("$tableACs.*")
            ->join($tableAAs, "$tableAAs.id", "$tableACs.assessment_answer_id")
            ->where("$tableAAs.assessment_id", $assessment->id);

        $evidencePath = 'images/placeholder.jpg';
        $exampleAnswers = [];
        foreach ($assCriteriasQuery->cursor() as $assCriteria) {
            $item = [
                'criteria_id' => $assCriteria->question_criteria_id,
                ...$assCriteria->only(['value', 'evidence_path', 'note']),
            ];

            if ($assCriteria->value === true) {
                $exampleAnswers[] = $item;
                continue;
            }

            $item['value'] = $assCriteria->id % 3 === 2;
            if ($item['value'] && !$item['evidence_path']) {
                $item['evidence_path'] = $evidencePath;
                $item['note'] = null;
            } else {
                $item['note'] = 'Test tidak diisi.';
            }

            $exampleAnswers[] = $item;
        }

        return $this->success(
            message: 'On Desk Assessment:example store body generated successfully.',
            data: [
                'answers' => $exampleAnswers,
            ]
        );
    }
}