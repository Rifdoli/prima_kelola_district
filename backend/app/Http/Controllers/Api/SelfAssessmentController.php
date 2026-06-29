<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\Organization;
use App\Models\OrganizationMapping;
use App\Models\SelfAssessment;
use App\Models\SelfAssessmentAnswer;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SelfAssessmentController extends Controller
{
    use ApiResponse;

    /**
     * organization_id yang boleh DILIHAT user ini.
     * - admin_sup: semua organisasi.
     * - lainnya: organisasi sendiri + seluruh descendant (closure table
     *   organization_mapping, sudah termasuk diri sendiri karena ada
     *   baris self-reference depth=0).
     */
    private function visibleOrganizationIds(User $user): array
    {
        if ($user->role?->sname === 'admin_sup') {
            return Organization::pluck('organization_id')->all();
        }

        return OrganizationMapping::where('ancestor_id', $user->organization_id)
            ->pluck('descendant_id')
            ->all();
    }

    private function isDistrictUser(User $user): bool
    {
        return $user->role?->sname === 'admin_dis';
    }

    /**
     * Pastikan user adalah pemilik district & assessment masih bisa diedit.
     */
    private function ensureEditable(User $user, SelfAssessment $assessment): ?JsonResponse
    {
        if (! $this->isDistrictUser($user) || $assessment->organization_id !== $user->organization_id) {
            return $this->error('Forbidden.', 403);
        }

        if ($assessment->status === 'submitted') {
            return $this->error('Self assessment sudah disubmit dan tidak dapat diubah.', 422);
        }

        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $list = SelfAssessment::with('organization', 'submittedBy')
            ->whereIn('organization_id', $this->visibleOrganizationIds($user))
            ->when($request->query('period'), fn ($q, $period) => $q->where('period', $period))
            ->orderByDesc('self_assessment_id')
            ->get();

        return $this->success($list);
    }

    /**
     * Get-or-create record periode untuk organisasi milik user (district saja).
     * Status awal 'open'.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $this->isDistrictUser($user) || ! $user->organization_id) {
            return $this->error('Hanya District Manager yang dapat membuat self assessment.', 403);
        }

        $validated = $request->validate([
            'period' => ['required', 'string', 'regex:/^\d{4}-Q[1-4]$/'],
        ]);

        $assessment = SelfAssessment::firstOrCreate(
            ['organization_id' => $user->organization_id, 'period' => $validated['period']],
            ['status' => 'open']
        );

        return $this->success($assessment->load('answers'), 'Self assessment siap diisi.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, SelfAssessment $selfAssessment)
    {
        $user = $request->user();

        if (! in_array($selfAssessment->organization_id, $this->visibleOrganizationIds($user))) {
            return $this->error('Forbidden.', 403);
        }

        return $this->success(
            $selfAssessment->load('organization', 'submittedBy', 'answers.question')
        );
    }

    /**
     * Simpan/update jawaban (bulk). Hanya pemilik district & status belum submitted.
     * open -> draft.
     */
    public function saveAnswers(Request $request, SelfAssessment $selfAssessment)
    {
        $user = $request->user();

        if ($response = $this->ensureEditable($user, $selfAssessment)) {
            return $response;
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.assessment_question_id' => ['required', 'exists:assessment_questions,assessment_question_id'],
            'answers.*.achieved_level' => ['nullable', 'in:A,B,C,D,E'],
            'answers.*.evidence_note' => ['nullable', 'string'],
        ]);

        foreach ($validated['answers'] as $answer) {
            SelfAssessmentAnswer::updateOrCreate(
                [
                    'self_assessment_id' => $selfAssessment->getKey(),
                    'assessment_question_id' => $answer['assessment_question_id'],
                ],
                [
                    'achieved_level' => $answer['achieved_level'] ?? null,
                    'evidence_note' => $answer['evidence_note'] ?? null,
                ]
            );
        }

        if ($selfAssessment->status === 'open') {
            $selfAssessment->update(['status' => 'draft']);
        }

        return $this->success($selfAssessment->load('answers'), 'Jawaban disimpan.');
    }

    /**
     * Upload file evidence untuk 1 jawaban (multipart, field 'file').
     */
    public function uploadEvidence(Request $request, SelfAssessment $selfAssessment, AssessmentQuestion $assessmentQuestion)
    {
        $user = $request->user();

        if ($response = $this->ensureEditable($user, $selfAssessment)) {
            return $response;
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // max 5MB
        ]);

        $answer = SelfAssessmentAnswer::firstOrNew([
            'self_assessment_id' => $selfAssessment->getKey(),
            'assessment_question_id' => $assessmentQuestion->getKey(),
        ]);

        // hapus file lama kalau ada (ganti file)
        if ($answer->evidence_file) {
            Storage::disk('public')->delete($answer->evidence_file);
        }

        $path = $request->file('file')->store(
            'evidence/'.$selfAssessment->getKey(),
            'public'
        );
        $answer->evidence_file = $path;
        $answer->save();

        if ($selfAssessment->status === 'open') {
            $selfAssessment->update(['status' => 'draft']);
        }

        return $this->success($answer->fresh(), 'File evidence diupload.');
    }

    /**
     * Submit & hitung skor akhir. Boleh dari open/draft. Setelah ini terkunci.
     */
    public function submit(Request $request, SelfAssessment $selfAssessment)
    {
        $user = $request->user();

        if (! $this->isDistrictUser($user) || $selfAssessment->organization_id !== $user->organization_id) {
            return $this->error('Forbidden.', 403);
        }

        if ($selfAssessment->status === 'submitted') {
            return $this->error('Self assessment sudah disubmit sebelumnya.', 422);
        }

        $levelToScore = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];
        $answers = $selfAssessment->answers()->with('question')->get();

        $totalMax = $answers->sum(fn ($a) => $a->question->max_score);
        $totalScore = $answers->sum(fn ($a) => $levelToScore[$a->achieved_level] ?? 0);

        $selfAssessment->update([
            'status' => 'submitted',
            'submitted_by' => $user->getKey(),
            'submitted_at' => now(),
            'total_score' => $totalMax > 0 ? round($totalScore / $totalMax * 100, 2) : 0,
        ]);

        return $this->success($selfAssessment->fresh(), 'Self assessment berhasil disubmit.');
    }
}
