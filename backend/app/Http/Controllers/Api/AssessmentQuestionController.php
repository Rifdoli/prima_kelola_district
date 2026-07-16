<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AssessmentQuestionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(
            message: 'Assessment Questions retrieved successfully.',
            data: AssessmentQuestion::orderBy('sort_order')->get(),
        );
    }

    public function show(int $id)
    {
        return $this->success(
            message: 'Assessment Question retrieved successfully.',
            data: AssessmentQuestion::withTrashed()->findOrFail($id),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'practice_area' => ['required', 'string', 'max:255'],
            'scope' => ['nullable', 'string', 'max:255'],
            'perangkat' => ['nullable', 'string', 'max:255'],
            'question' => ['required', 'string'],
            'criteria_a' => ['required', 'string'],
            'criteria_b' => ['required', 'string'],
            'criteria_c' => ['required', 'string'],
            'criteria_d' => ['required', 'string'],
            'criteria_e' => ['required', 'string'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $question = AssessmentQuestion::create([
            ...$validated,
            'weight_domain' => null,
            'references' => null,
            'weight_practice_area' => null,
            'max_score' => 5,
        ]);

        return $this->success(
            message: 'Assessment Question created successfully.',
            data: $question,
        );
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'practice_area' => ['required', 'string', 'max:255'],
            'scope' => ['nullable', 'string', 'max:255'],
            'perangkat' => ['nullable', 'string', 'max:255'],
            'question' => ['required', 'string'],
            'criteria_a' => ['required', 'string'],
            'criteria_b' => ['required', 'string'],
            'criteria_c' => ['required', 'string'],
            'criteria_d' => ['required', 'string'],
            'criteria_e' => ['required', 'string'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $question = AssessmentQuestion::withTrashed()->findOrFail($id);
        $question->update([
            ...$validated,
            'weight_domain' => null,
            'references' => null,
            'weight_practice_area' => null,
            'max_score' => 5,
        ]);

        return $this->success(
            message: 'Assessment Question updated successfully.',
            data: $question->fresh(),
        );
    }

    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'force' => ['nullable', 'boolean'],
        ]);

        $question = AssessmentQuestion::withTrashed()->findOrFail($id);
        if ($validated['force'] ?? false) {
            $question->forceDelete();
            return $this->success(
                message: 'Assessment Question permanently deleted successfully.',
            );
        }

        if (!$question->trashed()) $question->delete();
        return $this->success(
            message: 'Assessment Question archived successfully.',
        );
    }

    public function showArchives()
    {
        return $this->success(
            message: 'Assessment Questions retrieved successfully.',
            data: AssessmentQuestion::onlyTrashed()->orderBy('sort_order')->get(),
        );
    }

    public function restoreArchives(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $query = AssessmentQuestion::onlyTrashed();
        if (!empty($validated['ids'])) {
            $query->whereKey($validated['ids']);
        }

        $restoredCount = $query->restore();
        return $this->success(
            message: 'Assessment Questions restored successfully.',
            data: [ 'restored_count' => $restoredCount ],
        );
    }

    public function clearArchives(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $query = AssessmentQuestion::onlyTrashed();
        if (!empty($validated['ids'])) {
            $query->whereKey($validated['ids']);
        }

        $deletedCount = $query->forceDelete();
        return $this->success(
            message: 'Assessment Questions permanently deleted successfully.',
            data: [ 'deleted_count' => $deletedCount ],
        );
    }
}
