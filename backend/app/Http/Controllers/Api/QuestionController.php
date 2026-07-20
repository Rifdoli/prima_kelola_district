<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Question;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionCriteriasRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(
            message: 'Questions retrieved successfully.',
            data: $this->getQuestions(),
        );
    }

    public function show(int $id)
    {
        return $this->success(
            message: 'Question retrieved successfully.',
            data: $this->findQuestion($id),
        );
    }

    public function store(StoreQuestionRequest $request)
    {
        $data = $request->validated();
        $payload = Arr::except($data, 'criterias');

        $criteriasPayload = [];
        $criteriaSortOrder = 0;
        foreach ($data['criterias'] as $c) {
            $criteriaSortOrder++;
            $criteriasPayload[] = [ ...$c, 'sort_order' => $criteriaSortOrder ];
        }

        $question = DB::transaction(function () use ($payload, $criteriasPayload) {
            $payload['max_score'] = count($criteriasPayload);
            $payload['sort_order'] ??= (Question::max('sort_order') ?? 0) + 1;

            $question = Question::create($payload);
            $criterias = $question->criterias()->createMany($criteriasPayload);
            $question->setRelation('criterias', $criterias);
            return $question;
        });

        $question->load(['practiceArea.domain']);
        return $this->success(
            message: 'Question created successfully.',
            data: $this->formatQuestion($question),
            status: 201,
        );
    }

    public function update(UpdateQuestionRequest $request, int $id)
    {
        $payload = $request->validated();
        $question = Question::withTrashed()->findOrFail($id);
        $question->update($payload);
        $question->load(['practiceArea.domain', 'criterias']);

        return $this->success(
            message: 'Question updated successfully.',
            data: $this->formatQuestion($question),
        );
    }

    public function updateCriterias(UpdateQuestionCriteriasRequest $request, int $id)
    {
        $question = Question::withTrashed()->with('criterias')->findOrFail($id);
        $criteriasPayload = $request->validated('criterias');
        DB::transaction(function () use ($question, $criteriasPayload) {
            $existing = $question->criterias->keyBy('id');

            $invalidMsgs = [];
            foreach ($criteriasPayload as $index => $payload) {
                $id = $payload['id'] ?? null;
                if ($id && !$existing->has($id)) {
                    $invalidMsgs["criterias.$index.id"][] = "Criteria with id {$id} was not found.";
                }
            }

            if ($invalidMsgs) {
                throw ValidationException::withMessages($invalidMsgs);
            }

            $keepIds = [];
            foreach ($criteriasPayload as $index => $payload) {
                $id = $payload['id'] ?? null;
                $criteria = $id ? $existing[$id] : null;
                $attributes = [
                    'code' => $payload['code'],
                    'title' => $payload['title'],
                    'sort_order' => $index + 1,
                ];

                if ($criteria) {
                    $criteria->update($attributes);
                } else {
                    $criteria = $question->criterias()->create($attributes);
                }

                $keepIds[] = $criteria->id;
            }

            $question->criterias()->whereNotIn('id', $keepIds)->delete();
            $question->update([ 'max_score' => count($criteriasPayload) ]);
        });

        $question->load(['practiceArea.domain', 'criterias']);
        return $this->success(
            message: 'Question updated successfully.',
            data: $this->formatQuestion($question),
        );
    }

    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'force' => ['nullable', 'boolean'],
        ]);

        $question = Question::withTrashed()->findOrFail($id);
        if ($validated['force'] ?? false) {
            $question->forceDelete();
            return $this->success(
                message: 'Question permanently deleted successfully.',
            );
        }

        if (!$question->trashed()) $question->delete();
        return $this->success(
            message: 'Question archived successfully.',
        );
    }

    public function showTrashes()
    {
        return $this->success(
            message: 'Questions retrieved successfully.',
            data: $this->getQuestions(onlyTrashed: true),
        );
    }

    public function showTrash(int $id)
    {
        return $this->success(
            message: 'Question retrieved successfully.',
            data: $this->findQuestion(id: $id, onlyTrashed: true),
        );
    }

    public function restoreTrashes(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $query = Question::onlyTrashed();
        if (!empty($validated['ids'])) {
            $query->whereKey($validated['ids']);
        }

        $restoredCount = $query->restore();
        return $this->success(
            message: 'Questions restored successfully.',
            data: [ 'restored_count' => $restoredCount ],
        );
    }

    public function cleanTrashes(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $query = Question::onlyTrashed();
        if (!empty($validated['ids'])) {
            $query->whereKey($validated['ids']);
        }

        $deletedCount = $query->forceDelete();
        return $this->success(
            message: 'Questions permanently deleted successfully.',
            data: [ 'deleted_count' => $deletedCount ],
        );
    }

    private function getQuestions(bool $onlyTrashed = false): array
    {
        $query = Question::with(['practiceArea.domain', 'criterias']);
        if ($onlyTrashed) $query->onlyTrashed();
        $lazyCollections = $query->orderBy('sort_order')->lazy();

        $tree = [];
        foreach ($lazyCollections as $item) {
            $domainId = $item->practiceArea->domain->id;
            if (!isset($tree[$domainId])) {
                $tree[$domainId] = [];
            }

            $practiceAreaId = $item->practiceArea->id;
            if (!isset($tree[$domainId][$practiceAreaId])) {
                $tree[$domainId][$practiceAreaId] = [];
            }

            $tree[$domainId][$practiceAreaId][] = $this->formatQuestion($item);
        }

        $questions = [];
        foreach ($tree as $xItems) {
            foreach ($xItems as $yItems) {
                foreach ($yItems as $item) {
                    $questions[] = $item;
                }
            }
        }

        return $questions;
    }

    private function findQuestion(int $id, bool $onlyTrashed = false): array
    {
        $query = Question::with(['practiceArea.domain', 'criterias']);
        if ($onlyTrashed) $query->onlyTrashed();
        return $this->formatQuestion( $query->findOrFail($id) );
    }

    private function formatQuestion(Question $question): array
    {
        return [
            'id' => $question->id,
            'question' => $question->question,
            'scope' => $question->scope,
            'perangkat' => $question->perangkat,
            'max_score' => $question->max_score,
            'sort_order' => $question->sort_order,
            'domain' => [
                'id' => $question->practiceArea->domain->id,
                'name' => $question->practiceArea->domain->name,
                'weight' => $question->practiceArea->domain->weight,
            ],
            'practice_area' => [
                'id' => $question->practiceArea->id,
                'name' => $question->practiceArea->name,
                'weight' => $question->practiceArea->weight,
            ],
            'criterias' => $question->criterias
                ->map(fn ($criteria) => [
                    'id' => $criteria->id,
                    'code' => $criteria->code,
                    'sort_order' => $criteria->sort_order,
                    'title' => $criteria->title,
                    'reference' => $criteria->reference,
                    'evidence_hint' => $criteria->evidence_hint,
                ])
                ->all(),
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
            'deleted_at' => $question->deleted_at,
            'created_by' => $question->created_by,
            'updated_by' => $question->updated_by,
        ];
    }
}