<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentVerification;
use App\Models\AssessmentVerificationLevel;
use App\Models\Organization;
use App\Models\OrganizationMapping;
use App\Models\SelfAssessment;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    use ApiResponse;

    private const LEVELS = ['A', 'B', 'C', 'D', 'E'];

    /**
     * Role yang boleh mengerjakan tiap tipe verifikasi.
     * ODA: Regional. OSA: Area/Nasional. Super selalu boleh.
     */
    private function allowedRoles(string $type): array
    {
        return $type === 'on_desk' ? ['admin_reg'] : ['admin_are', 'admin_nas'];
    }

    private function canPerform(User $user, string $type): bool
    {
        $sname = $user->role?->sname;

        return $sname === 'admin_sup' || in_array($sname, $this->allowedRoles($type), true);
    }

    /**
     * Pesan penolakan yang seragam & spesifik per tipe.
     */
    private function roleMessage(string $type): string
    {
        return $type === 'on_desk'
            ? 'Hanya Regional yang dapat melakukan On Desk Assessment.'
            : 'Hanya Area/Nasional yang dapat melakukan On Site Assessment.';
    }

    /**
     * organization_id yang boleh dilihat/diverifikasi user (dirinya + descendant).
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

    private function assertType(string $type): ?JsonResponse
    {
        if (! in_array($type, ['on_desk', 'on_site'], true)) {
            return $this->error('Tipe verifikasi tidak valid.', 404);
        }

        return null;
    }

    /**
     * Pastikan user boleh mengedit verifikasi ini (role sesuai tipe, district
     * dalam jangkauan, dan belum disubmit).
     */
    private function ensureEditable(User $user, AssessmentVerification $verification): ?JsonResponse
    {
        if (! $this->canPerform($user, $verification->type)) {
            return $this->error($this->roleMessage($verification->type), 403);
        }

        $districtOrgId = $verification->selfAssessment->organization_id;
        if (! in_array($districtOrgId, $this->visibleOrganizationIds($user), true)) {
            return $this->error('Forbidden.', 403);
        }

        if ($verification->status === 'submitted') {
            return $this->error('Verifikasi sudah disubmit dan tidak dapat diubah.', 422);
        }

        return null;
    }

    /**
     * Daftar Self Assessment yang siap diverifikasi (submitted) untuk tipe ini,
     * beserta status verifikasinya. Untuk OSA hanya yang ODA-nya sudah submitted.
     */
    public function index(Request $request, string $type)
    {
        if ($response = $this->assertType($type)) {
            return $response;
        }

        $user = $request->user();
        if (! $this->canPerform($user, $type)) {
            return $this->error($this->roleMessage($type), 403);
        }

        $orgIds = $this->visibleOrganizationIds($user);

        $list = SelfAssessment::with(['organization', 'verifications'])
            ->where('status', 'submitted')
            ->whereIn('organization_id', $orgIds)
            ->when($request->query('period'), fn ($q, $period) => $q->where('period', $period))
            ->orderByDesc('self_assessment_id')
            ->get()
            ->filter(function (SelfAssessment $sa) use ($type) {
                // OSA hanya untuk self yang ODA-nya sudah submitted.
                if ($type === 'on_site') {
                    return $sa->verifications->first(
                        fn ($v) => $v->type === 'on_desk' && $v->status === 'submitted'
                    ) !== null;
                }

                return true;
            })
            ->map(function (SelfAssessment $sa) use ($type) {
                $verification = $sa->verifications->firstWhere('type', $type);
                // Untuk OSA, sertakan hasil ODA (parent) sebagai konteks.
                $oda = $type === 'on_site' ? $sa->verifications->firstWhere('type', 'on_desk') : null;

                return [
                    'self_assessment_id' => $sa->self_assessment_id,
                    'period' => $sa->period,
                    'organization' => $sa->organization,
                    'self_status' => $sa->status,
                    'self_score' => $sa->total_score,
                    'oda_status' => $oda?->status,
                    'oda_score' => $oda?->total_score,
                    'verification_status' => $verification?->status ?? 'not_started',
                    'verification_score' => $verification?->total_score,
                    'assessment_verification_id' => $verification?->assessment_verification_id,
                ];
            })
            ->values();

        return $this->success($list);
    }

    /**
     * Get-or-create verifikasi untuk sebuah Self Assessment + tipe.
     * Saat baru dibuat: seed level valid dari tahap sebelumnya (carry-forward).
     */
    public function store(Request $request, string $type)
    {
        if ($response = $this->assertType($type)) {
            return $response;
        }

        $user = $request->user();
        if (! $this->canPerform($user, $type)) {
            return $this->error($this->roleMessage($type), 403);
        }

        $validated = $request->validate([
            'self_assessment_id' => ['required', 'exists:self_assessments,self_assessment_id'],
        ]);

        $selfAssessment = SelfAssessment::with('answers')->findOrFail($validated['self_assessment_id']);

        if (! in_array($selfAssessment->organization_id, $this->visibleOrganizationIds($user), true)) {
            return $this->error('Forbidden.', 403);
        }

        if ($selfAssessment->status !== 'submitted') {
            return $this->error('Self Assessment belum disubmit district.', 422);
        }

        // OSA butuh ODA yang sudah submitted sebagai induk.
        $parent = null;
        if ($type === 'on_site') {
            $parent = AssessmentVerification::where('self_assessment_id', $selfAssessment->getKey())
                ->where('type', 'on_desk')
                ->where('status', 'submitted')
                ->first();

            if (! $parent) {
                return $this->error('On Desk Assessment belum disubmit.', 422);
            }
        }

        $verification = AssessmentVerification::firstOrNew([
            'self_assessment_id' => $selfAssessment->getKey(),
            'type' => $type,
        ]);

        if (! $verification->exists) {
            $verification->parent_verification_id = $parent?->getKey();
            $verification->status = 'open';
            $verification->save();

            $this->seedLevels($verification, $selfAssessment, $parent);
        }

        return $this->success($this->loadDetail($verification), 'Verifikasi siap dikerjakan.', 201);
    }

    /**
     * Seed level is_valid dari tahap sebelumnya sebagai titik awal.
     * ODA: dari klaim Self (achieved_levels). OSA: dari level valid ODA.
     */
    private function seedLevels(AssessmentVerification $verification, SelfAssessment $selfAssessment, ?AssessmentVerification $parent): void
    {
        $rows = [];

        if ($parent) {
            $validLevels = $parent->levels()->where('is_valid', true)->get();
            foreach ($validLevels as $lvl) {
                $rows[] = [
                    'assessment_question_id' => $lvl->assessment_question_id,
                    'level' => $lvl->level,
                ];
            }
        } else {
            foreach ($selfAssessment->answers as $answer) {
                foreach ($answer->achieved_levels ?? [] as $level) {
                    $rows[] = [
                        'assessment_question_id' => $answer->assessment_question_id,
                        'level' => $level,
                    ];
                }
            }
        }

        foreach ($rows as $row) {
            AssessmentVerificationLevel::create([
                'assessment_verification_id' => $verification->getKey(),
                'assessment_question_id' => $row['assessment_question_id'],
                'level' => $row['level'],
                'is_valid' => true,
            ]);
        }
    }

    /**
     * Muat verifikasi + konteks (klaim Self, level, induk ODA bila OSA).
     */
    private function loadDetail(AssessmentVerification $verification): AssessmentVerification
    {
        $verification->load([
            'levels',
            'selfAssessment.organization',
            'selfAssessment.answers.question',
            'parent.levels',
        ]);

        return $verification;
    }

    public function show(Request $request, AssessmentVerification $assessmentVerification)
    {
        $user = $request->user();

        if (! $this->canPerform($user, $assessmentVerification->type)) {
            return $this->error($this->roleMessage($assessmentVerification->type), 403);
        }

        $districtOrgId = $assessmentVerification->selfAssessment->organization_id;
        if (! in_array($districtOrgId, $this->visibleOrganizationIds($user), true)) {
            return $this->error('Forbidden.', 403);
        }

        return $this->success($this->loadDetail($assessmentVerification));
    }

    /**
     * Simpan hasil verifikasi per level (bulk). open -> draft.
     */
    public function saveLevels(Request $request, AssessmentVerification $assessmentVerification)
    {
        $user = $request->user();

        if ($response = $this->ensureEditable($user, $assessmentVerification)) {
            return $response;
        }

        $validated = $request->validate([
            'levels' => ['required', 'array'],
            'levels.*.assessment_question_id' => ['required', 'exists:assessment_questions,assessment_question_id'],
            'levels.*.level' => ['required', 'in:A,B,C,D,E'],
            'levels.*.is_valid' => ['required', 'boolean'],
            'levels.*.note' => ['nullable', 'string'],
        ]);

        foreach ($validated['levels'] as $level) {
            AssessmentVerificationLevel::updateOrCreate(
                [
                    'assessment_verification_id' => $assessmentVerification->getKey(),
                    'assessment_question_id' => $level['assessment_question_id'],
                    'level' => $level['level'],
                ],
                [
                    'is_valid' => $level['is_valid'],
                    'note' => $level['note'] ?? null,
                ]
            );
        }

        if ($assessmentVerification->status === 'open') {
            $assessmentVerification->update(['status' => 'draft']);
        }

        return $this->success($this->loadDetail($assessmentVerification), 'Verifikasi disimpan.');
    }

    public function uploadEvidence(Request $request, AssessmentVerification $assessmentVerification, AssessmentQuestion $assessmentQuestion, string $level)
    {
        $user = $request->user();

        if ($response = $this->ensureEditable($user, $assessmentVerification)) {
            return $response;
        }

        if (! in_array($level, self::LEVELS, true)) {
            return $this->error('Level tidak valid.', 422);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $row = AssessmentVerificationLevel::firstOrNew([
            'assessment_verification_id' => $assessmentVerification->getKey(),
            'assessment_question_id' => $assessmentQuestion->getKey(),
            'level' => $level,
        ]);

        if ($row->evidence_file) {
            Storage::disk('public')->delete($row->evidence_file);
        }

        $row->evidence_file = $request->file('file')->store(
            'verification-evidence/'.$assessmentVerification->getKey(),
            'public'
        );
        $row->save();

        if ($assessmentVerification->status === 'open') {
            $assessmentVerification->update(['status' => 'draft']);
        }

        return $this->success($row->fresh(), 'File evidence diupload.');
    }

    public function deleteEvidence(Request $request, AssessmentVerification $assessmentVerification, AssessmentQuestion $assessmentQuestion, string $level)
    {
        $user = $request->user();

        if ($response = $this->ensureEditable($user, $assessmentVerification)) {
            return $response;
        }

        $row = AssessmentVerificationLevel::where([
            'assessment_verification_id' => $assessmentVerification->getKey(),
            'assessment_question_id' => $assessmentQuestion->getKey(),
            'level' => $level,
        ])->first();

        if ($row && $row->evidence_file) {
            Storage::disk('public')->delete($row->evidence_file);
            $row->evidence_file = null;
            $row->save();
        }

        return $this->success($row?->fresh(), 'File evidence dihapus.');
    }

    /**
     * Submit & hitung skor. Skor = jumlah level valid / total max_score * 100.
     */
    public function submit(Request $request, AssessmentVerification $assessmentVerification)
    {
        $user = $request->user();

        if ($response = $this->ensureEditable($user, $assessmentVerification)) {
            return $response;
        }

        $validCount = $assessmentVerification->levels()->where('is_valid', true)->count();
        $totalMax = AssessmentQuestion::sum('max_score');

        $assessmentVerification->update([
            'status' => 'submitted',
            'verified_by' => $user->getKey(),
            'submitted_at' => now(),
            'total_score' => $totalMax > 0 ? round($validCount / $totalMax * 100, 2) : 0,
        ]);

        return $this->success($this->loadDetail($assessmentVerification), 'Verifikasi berhasil disubmit.');
    }
}
