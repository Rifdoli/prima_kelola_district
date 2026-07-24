<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionCriteria;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCriteria;
use App\Http\Requests\SelfAssessment\GetAssessmentRequest;
use App\Http\Requests\SelfAssessment\StoreAssessmentAnswersRequest;
use App\Http\Requests\SelfAssessment\StoreAssessmentEvidenceRequest;
// use App\Http\Requests\SelfAssessment\DestroyAssessmentEvidenceRequest;
use App\Traits\ApiResponse;
use App\Rules\AssessmentPeriod;
use App\Services\Evidence\AssessmentEvidence;
use App\Exceptions\Assessment\AssessmentLockedException;
use App\Exceptions\Assessment\InvalidQCriteriaException;
use App\Support\AssessmentScore;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;

/**
 * Fitur delete evidence oleh User belum dinyalakan.
 *
 * Masih Perlu autorisasi lebih untuk mencegah menghapus
 * evidence yang terikat ke assessment lain di luar scope user.
 * Lebih secure jika semua evidence deletion dikelola langsung
 * oleh Sistem yang berjalan di Queue.
 */
class AssessmentSelfController extends Controller
{
    use ApiResponse;

    public function index(GetAssessmentRequest $request, string $period)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $params = $request->validated();

        $orgId = auth()->user()->organization_id;
        $type = Assessment::TYPE_SA;
        $assessment = Assessment::with('answers.criterias')
            ->where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', $type)
            ->first();

        /** @var ?array */
        $criteriaAnswersMap = null;
        $checkedQuestionIds = null;
        $isNewAssessmentCreated = false;
        if (!$assessment) {

            $isNewAssessmentCreated = true;
            $assessment = Assessment::create([
                'organization_id' => $orgId,
                'period' => $period,
                'type' => Assessment::TYPE_SA,
                'status' => Assessment::STATUS_OPEN,
                'is_active' => false,
                'total_score' => null,
            ]);

        } else {

            $assessment->answers->each(function (AssessmentAnswer $assAnswer)
                use (&$checkedQuestionIds, &$criteriaAnswersMap)
            {
                $checkedQuestionIds[] = $assAnswer->question_id;
                foreach ($assAnswer->criterias->all() as $assCriteria) {
                    $evidencePath = $assCriteria->evidence_path;
                    $evidenceUrl = !$evidencePath ? null
                        : Storage::disk('public')->url($assCriteria->evidence_path);

                    if ($criteriaAnswersMap === null) {
                        $criteriaAnswersMap = [];
                    }

                    $criteriaAnswersMap[$assCriteria->question_criteria_id] = [
                        'id' => $assCriteria->id,
                        'value' => $assCriteria->value,
                        'evidence_path' => $evidencePath,
                        'evidence_url' => $evidenceUrl,
                        'note' => $assCriteria->note,
                        'criteria_id' => $assCriteria->question_criteria_id,
                        'question_id' => $assAnswer->question_id,
                        'practice_area_id' => null,
                        'domain_id' => null,
                    ];
                }
            });
        }

        $questionsQuery = Question::with(['practiceArea.domain', 'criterias']);
        if ($assessment->status == Assessment::STATUS_SUBMITTED) {
            // assessment yang sudah disubmit ditampilkan apa adanya saat dinilai,
            // termasuk soal & kriteria yang sejak itu diarsipkan
            $questionsQuery->withTrashed()
                ->with(['criterias' => fn ($query) => $query->withTrashed()]);
        }

        if (!empty($params['domain_ids'])) {
            $questionsQuery->whereHas('practiceArea', function ($query) use ($params) {
                $query->whereIn('parent_id', $params['domain_ids']);
            });
        }

        if (!empty($params['practice_area_ids'])) {
            $questionsQuery->whereIn('practice_area_id', $params['practice_area_ids']);
        }

        if (!empty($params['question_ids'])) {
            $questionsQuery->whereIn('id', $params['question_ids']);
        }

        $tree = [];
        $questionsQuery->orderBy('sort_order')
            ->lazy()
            ->each(function ($item) use (&$tree, $checkedQuestionIds, &$criteriaAnswersMap) {
                $questionId = $item->id;
                if ($checkedQuestionIds && !in_array($questionId, $checkedQuestionIds)) {
                    return;
                }

                $domainId = $item->practiceArea->domain->id;
                if (!isset($tree[$domainId])) {
                    $tree[$domainId] = [
                        'domain' => [
                            'id' => $domainId,
                            'name' => $item->practiceArea->domain->name,
                            'weight' => $item->practiceArea->domain->weight,
                        ],
                        'practice_areas' => []
                    ];
                }

                $practiceAreaId = $item->practiceArea->id;
                if (!isset($tree[$domainId]['practice_areas'][$practiceAreaId])) {
                    $tree[$domainId]['practice_areas'][$practiceAreaId] = [
                        'practice_area' => [
                            'id' => $practiceAreaId,
                            'name' => $item->practiceArea->name,
                            'weight' => $item->practiceArea->weight,
                            'domain_id' => $domainId,
                        ],
                        'questions' => []
                    ];
                }

                $questionTree = [
                    'question' => [
                        'id' => $questionId,
                        'question' => $item->question,
                        'scope' => $item->scope,
                        'perangkat' => $item->perangkat,
                        'max_score' => $item->max_score,
                        'sort_order' => $item->sort_order,
                        'practice_area_id' => $practiceAreaId,
                        'domain_id' => $domainId,
                    ],
                    'criterias' => [],
                ];

                foreach($item->criterias->all() as $criteria) {
                    $questionTree['criterias'][] = [
                        'id' => $criteria->id,
                        'code' => $criteria->code,
                        'sort_order' => $criteria->sort_order,
                        'title' => $criteria->title,
                        'reference' => $criteria->reference,
                        'evidence_hint' => $criteria->evidence_hint,
                        'question_id' => $questionId,
                        'practice_area_id' => $practiceAreaId,
                        'domain_id' => $domainId,
                    ];

                    if ($criteriaAnswersMap !== null) {
                        $criteriaAnswersMap[$criteria->id]['practice_area_id'] = $practiceAreaId;
                        $criteriaAnswersMap[$criteria->id]['domain_id'] = $domainId;
                    }
                }

                $tree[$domainId]['practice_areas'][$practiceAreaId]['questions'][] = $questionTree;
            });

        $domains = [];
        $practiceAreas = [];
        $questions = [];
        $criterias = [];
        $answers = [];
        foreach ($tree as $d) {
            $domains[] = $d['domain'];
            foreach ($d['practice_areas'] as $p) {
                $practiceAreas[] = $p['practice_area'];
                foreach ($p['questions'] as $q) {
                    $questions[] = $q['question'];
                    foreach ($q['criterias'] as $criteria) {
                        $criterias[] = $criteria;
                        if ($criteriaAnswersMap !== null) {
                            $answers[] = $criteriaAnswersMap[$criteria['id']];
                        }
                    }
                }
            }
        }

        unset($tree, $criteriaAnswersMap, $checkedQuestionIds);
        $message = 'Self Assessment siap diisi.';
        $status = 201;
        if ($assessment->status == Assessment::STATUS_SUBMITTED) {
            $message = 'Self Assessment telah diisi.';
            $status = 200;
        } elseif (!$isNewAssessmentCreated) {
            $status = 200;
        }

        return $this->success(
            data: [
                'assessment' => $assessment->withoutRelations(),
                'domains' => $domains,
                'practice_areas' => $practiceAreas,
                'questions' => $questions,
                'criterias' => $criterias,
                'answers' => $answers,
            ],
            message: $message,
            status: $status
        );
    }

    public function store(StoreAssessmentAnswersRequest $request, string $period)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $newCriterias = array_column($request->validated('answers'), null, 'criteria_id');

        $isNewAssessmentCreated = false;
        $orgId = auth()->user()->organization_id;
        $assessment = Assessment::with('answers')
            ->where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_SA)
            ->first();
        if (!$assessment) {
            $isNewAssessmentCreated = true;
            $assessment = Assessment::create([
                'organization_id' => $orgId,
                'period' => $period,
                'type' => Assessment::TYPE_SA,
                'status' => Assessment::STATUS_OPEN,
                'is_active' => false,
                'total_score' => null,
            ]);
        }

        try {
            $this->saveAssessmentAnswers(
                assessment: $assessment,
                newCriterias: $newCriterias,
                storeAsDraft: false
            );
        } catch (AssessmentLockedException $e) {
            return $this->error(
                message: "Status Self Assessment sudah dikunci dan tidak dapat di-submit.",
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
            message: 'Self Assessment berhasil di-submit.',
            status: $isNewAssessmentCreated ? 201 : 200
        );
    }

    public function storeDraft(StoreAssessmentAnswersRequest $request, string $period)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $newCriterias = array_column($request->validated('answers'), null, 'criteria_id');

        $isNewAssessmentCreated = false;
        $orgId = auth()->user()->organization_id;
        $assessment = Assessment::with('answers')
            ->where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_SA)
            ->first();
        if (!$assessment) {
            $isNewAssessmentCreated = true;
            $assessment = Assessment::create([
                'organization_id' => $orgId,
                'period' => $period,
                'type' => Assessment::TYPE_SA,
                'status' => Assessment::STATUS_OPEN,
                'is_active' => false,
                'total_score' => null,
            ]);
        }

        try {
            $this->saveAssessmentAnswers(
                assessment: $assessment,
                newCriterias: $newCriterias,
                storeAsDraft: true
            );
        } catch (AssessmentLockedException $e) {
            return $this->error(
                message: "Status Self Assessment sudah dikunci dan tidak dapat di-submit.",
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
            message: 'Self Assessment berhasil disimpan sebagai draft.',
            status: $isNewAssessmentCreated ? 201 : 200
        );
    }

    /**
     * AssessmentEvidence service membutuhkan queue worker untuk
     * menjalankan `CleanupUnusedEvidenceJob`.
     */
    public function storeEvidence(StoreAssessmentEvidenceRequest $request, string $period)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();
        $body = $request->validated();

        $orgId = auth()->user()->organization_id;
        $assessment = Assessment::where('organization_id', $orgId)
            ->where('period', $period)
            ->where('type', Assessment::TYPE_SA)
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
        $message = 'Evidence berhasil diupload.';

        // $replacePath = $body['replace_path'] ?? null;
        // if ($replacePath) {
        //     try {
        //         $evidenceService->delete($replacePath);
        //     } catch (\Throwable $e) {
        //         Log::warning($e->getMessage(), [
        //             'replace_path' => $replacePath,
        //             'exception' => $e,
        //         ]);
        //         $message = "Evidence berhasil diupload, gagal menghapus evidence:'$replacePath'.";
        //     }
        // }

        return $this->success(
            data: ['path' => $evidence->path, 'url' => $evidence->url],
            message: $message,
            status: 201
        );
    }

    // public function destroyEvidence(DestroyAssessmentEvidenceRequest $request, string $period)
    // {
    //     Validator::make(
    //         ['period' => $period],
    //         ['period' => AssessmentPeriod::requiredRules()]
    //     )->validate();

    //     $body = $request->validate([
    //         'path' => ['required', 'string'],
    //     ]);

    //     $year = (int) substr($period, 0, 4);
    //     $quarter = (int) substr($period, -1);
    //     $evidenceService = (new AssessmentEvidence)
    //         ->setUploadYear($year)
    //         ->setUploadQuarter($quarter);
    //     $evidenceService->delete($body['path']);

    //     return $this->success(
    //         message: 'Evidence berhasil dihapus.'
    //     );
    // }

    private function saveAssessmentAnswers(
        Assessment $assessment,
        array $newCriterias,
        bool $storeAsDraft
    ): void {
        if ($assessment->status == Assessment::STATUS_SUBMITTED) {
            throw new AssessmentLockedException;
        }

        $matchedCriteriaIds = [];
        $assAnswersData = [];
        $assCriteriasMap = [];
        $scoreItems = [];
        Question::with(['practiceArea.domain', 'criterias'])
            ->orderBy('sort_order')
            ->lazy()
            ->each(function (Question $question)
                use ($assessment, $newCriterias, &$matchedCriteriaIds, &$assAnswersData, &$assCriteriasMap, &$scoreItems)
            {
                $assAnswersData[] = [
                    'assessment_id' => $assessment->id,
                    'question_id' => $question->id,
                ];

                $assCriteriaMap = [];
                $achieved = 0;
                foreach ($question->criterias as $criteria) {
                    if (isset($newCriterias[$criteria->id])) {

                        $newCriteria = $newCriterias[$criteria->id];
                        $value = filter_var($newCriteria['value'], FILTER_VALIDATE_BOOLEAN);
                        $assCriteriaMap[] = [
                            'question_criteria_id' => $criteria->id,
                            'value' => $value,
                            'evidence_path' => $newCriteria['evidence_path'],
                            'note' => $newCriteria['note'],
                        ];
                        if ($value) $achieved++;

                        $matchedCriteriaIds[$criteria->id] = $criteria->id;

                    } else {
                        $assCriteriaMap[] = [
                            'question_criteria_id' => $criteria->id,
                            'value' => false,
                            'evidence_path' => null,
                            'note' => null,
                        ];
                    }
                }

                $assCriteriasMap[$question->id] = $assCriteriaMap;

                $scoreItems[] = [
                    'domain' => $question->practiceArea->domain->name,
                    'practice_area' => $question->practiceArea->name,
                    'weight_domain' => (float) $question->practiceArea->domain->weight,
                    'weight_pa' => (float) $question->practiceArea->weight,
                    'achieved' => $achieved,
                    'max' => count($question->criterias),
                ];

            });

        if (count($newCriterias) > count($matchedCriteriaIds)) {
            foreach (array_keys($newCriterias) as $criteriaId) {
                if (!in_array($criteriaId, $matchedCriteriaIds)) {
                    throw (new InvalidQCriteriaException)->setCriteriaId($criteriaId);
                }
            }
        }

        $totalScore = AssessmentScore::weightedTotal($scoreItems);
        unset($newCriterias, $matchedCriteriaIds, $scoreItems);
        DB::transaction(function () use ($assessment, $storeAsDraft, $assAnswersData, $assCriteriasMap, $totalScore) {
            $oldAnswerIds = $assessment->answers->modelKeys();
            if ($oldAnswerIds) {
                // kriteria dulu: assessment_criterias.assessment_answer_id restrictOnDelete
                AssessmentCriteria::whereIn('assessment_answer_id', $oldAnswerIds)->delete();
                AssessmentAnswer::whereKey($oldAnswerIds)->delete();
            }

            AssessmentAnswer::fillAndInsert($assAnswersData);
            $assAnswers = AssessmentAnswer::where('assessment_id', $assessment->id)->get();

            $assCriteriasData = [];
            foreach ($assAnswers as $assAnswer) {
                foreach ($assCriteriasMap[$assAnswer->question_id] as $assCriteriaData) {
                    $assCriteriaData['assessment_answer_id'] = $assAnswer->id;
                    $assCriteriasData[] = $assCriteriaData;
                }
            }
            AssessmentCriteria::fillAndInsert($assCriteriasData);

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
                'type' => Assessment::TYPE_ODA,
                'status' => Assessment::STATUS_OPEN,
                'is_active' => true,
                'total_score' => $totalScore,
                'prev_assessment_id' => $assessment->id,
            ]);

            foreach ($assAnswersData as &$assAnswerData) {
                $assAnswerData['assessment_id'] = $newAssessment->id;
            }
            AssessmentAnswer::fillAndInsert($assAnswersData);
            $newAssAnswers = AssessmentAnswer::where('assessment_id', $newAssessment->id)->get();

            $assCriteriasData = [];
            foreach ($newAssAnswers as $assAnswer) {
                foreach ($assCriteriasMap[$assAnswer->question_id] as $assCriteriaData) {
                    $assCriteriaData['assessment_answer_id'] = $assAnswer->id;
                    $assCriteriasData[] = $assCriteriaData;
                }
            }
            AssessmentCriteria::fillAndInsert($assCriteriasData);
        });
    }

    public function showExampleAnswersBody(string $period)
    {
        Validator::make(
            ['period' => $period],
            ['period' => AssessmentPeriod::requiredRules()]
        )->validate();

        $year = (int) substr($period, 0, 4);
        $quarter = (int) substr($period, -1);
        $evidencePath = 'images/placeholder.jpg';

        $exampleAnswers = QuestionCriteria::whereHas('question')
            ->get()
            ->map(function (QuestionCriteria $criteria) use ($evidencePath) {
                $value = $criteria->id % 3 === 1;
                return [
                    'criteria_id' => $criteria->id,
                    'value' => $value,
                    'evidence_path' => $value ? $evidencePath : null,
                    'note' => $value ? null : 'Test tidak diisi.',
                ];
            });

        return $this->success(
            message: 'Self Assessment:example store body generated successfully.',
            data: [
                'answers' => $exampleAnswers,
            ]
        );
    }
}