<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationMapping;
use App\Models\SelfAssessment;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AssessmentTrackingController extends Controller
{
    use ApiResponse;

    /**
     * organization_id yang boleh dilihat user (dirinya + descendant).
     * ponytail: duplikasi kecil dari Self/Verification controller; dedup ke
     * trait bila logika visibilitas bertambah rumit.
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

    /**
     * Fase assessment saat ini berdasar status Self -> ODA -> OSA.
     */
    private function phase(string $selfStatus, ?string $odaStatus, ?string $osaStatus): string
    {
        if ($selfStatus !== 'submitted') {
            return 'Pengisian Self';
        }
        if ($odaStatus !== 'submitted') {
            return $odaStatus === 'draft' ? 'Proses ODA' : 'Menunggu ODA';
        }
        if ($osaStatus !== 'submitted') {
            return $osaStatus === 'draft' ? 'Proses OSA' : 'Menunggu OSA';
        }

        return 'Selesai';
    }

    /**
     * Daftar tracking assessment untuk semua district dalam jangkauan user.
     * District melihat miliknya sendiri, Regional/Area/Nasional melihat bawahannya.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $list = SelfAssessment::with(['organization', 'verifications'])
            ->whereIn('organization_id', $this->visibleOrganizationIds($user))
            ->when($request->query('period'), fn ($q, $period) => $q->where('period', $period))
            ->orderByDesc('self_assessment_id')
            ->get()
            ->map(function (SelfAssessment $sa) {
                $oda = $sa->verifications->firstWhere('type', 'on_desk');
                $osa = $sa->verifications->firstWhere('type', 'on_site');

                return [
                    'self_assessment_id' => $sa->self_assessment_id,
                    'period' => $sa->period,
                    'organization' => $sa->organization,
                    'phase' => $this->phase($sa->status, $oda?->status, $osa?->status),
                    'self_status' => $sa->status,
                    'self_score' => $sa->total_score,
                    'oda_status' => $oda?->status ?? 'not_started',
                    'oda_score' => $oda?->total_score,
                    'osa_status' => $osa?->status ?? 'not_started',
                    'osa_score' => $osa?->total_score,
                ];
            });

        return $this->success($list);
    }
}
