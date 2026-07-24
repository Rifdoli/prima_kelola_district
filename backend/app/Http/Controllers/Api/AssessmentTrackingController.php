<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentTracking\GetAssessmentsRequest;
use App\Models\Assessment;
use App\Models\Organization;
use App\Models\OrganizationMapping;
use App\Models\SelfAssessment;
use App\Models\User;
use App\Traits\ApiResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function backupIndex(Request $request)
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

    public function index(GetAssessmentsRequest $request)
    {
        $params = $request->safe()->only(['periods', 'organization_ids']);
        $orgIds = $this->getUserOrganizationScope($params['organization_ids'] ?? []);
        if ($orgIds === false) {
            return $this->success(
                data: [],
                message: 'Assessment Tracking data retrieved successfully.'
            );
        }

        $query = Assessment::with('organization')
            ->whereIn('period', $params['periods'])
            ->orderBy('period')
            ->orderBy('organization_id')
            ->orderByRaw('prev_assessment_id IS NOT NULL')
            ->orderBy('prev_assessment_id');

        if (is_array($orgIds)) {
            $query->whereIn('organization_id', $orgIds);
        } elseif (empty($params['organization_ids'])) {
            $query->whereRelation('organization', 'is_active', true);
        }

        $assessmentTracks = [];
        foreach ($query->lazy() as $assessment) {
            $key = "{$assessment->period}.{$assessment->organization_id}";
            if (!isset($assessmentTracks[$key])) {
                $assessmentTracks[$key] = [
                    'period' => $assessment->period,
                    'organization' => $assessment->organization,
                    'phase' => null,
                    'sa_status' => null,
                    'sa_score' => null,
                    'oda_status' => null,
                    'oda_score' => null,
                    'osa_status' => null,
                    'osa_score' => null,
                ];
            }

            if ($assessment->type == Assessment::TYPE_SA) {
                $assessmentTracks[$key]['sa_status'] = $assessment->status;
                $assessmentTracks[$key]['sa_score'] = $assessment->total_score;
                $assessmentTracks[$key]['phase'] = $this->phase(
                    selfStatus: $assessmentTracks[$key]['sa_status'],
                    odaStatus: null,
                    osaStatus: null
                );

                continue;
            }

            if ($assessment->type == Assessment::TYPE_ODA) {
                if ($assessmentTracks[$key]['sa_status'] === null) {
                    throw new \LogicException("failed to fill ODA:$key when SA still unfilled");
                }

                $assessmentTracks[$key]['oda_status'] = $assessment->status;
                $assessmentTracks[$key]['oda_score'] = $assessment->total_score;
                $assessmentTracks[$key]['phase'] = $this->phase(
                    selfStatus: $assessmentTracks[$key]['sa_status'],
                    odaStatus: $assessmentTracks[$key]['oda_status'],
                    osaStatus: null
                );

                continue;
            }

            if ($assessment->type == Assessment::TYPE_OSA) {
                if ($assessmentTracks[$key]['oda_status'] === null) {
                    throw new \LogicException("failed to fill OSA:$key when ODA still unfilled");
                }

                $assessmentTracks[$key]['osa_status'] = $assessment->status;
                $assessmentTracks[$key]['osa_score'] = $assessment->total_score;
                $assessmentTracks[$key]['phase'] = $this->phase(
                    selfStatus: $assessmentTracks[$key]['sa_status'],
                    odaStatus: $assessmentTracks[$key]['oda_status'],
                    osaStatus: $assessmentTracks[$key]['osa_status']
                );
            }
        }

        return $this->success(
            data: array_values($assessmentTracks),
            message: 'Assessment Tracking data retrieved successfully.'
        );
    }

    /**
     * @return (int[]|bool)
     *  - `true` while user is superadmin
     *  - `false` while no organizations matched
     *  - non-empty `int[]` while has matched organizations
     *
     * @todo Implement local static variable jika perlu digunakan berkali-kali
     */
    private function getUserOrganizationScope(array $targetOrgIds = []): array|bool
    {
        /** @var ?int */
        $userOrgId = auth()->user()->organization_id;
        if (!$userOrgId) {
            if (auth()->user()?->role?->sname !== 'admin_sup') {
                return false;
            }

            return empty($targetOrgIds) ? true : $targetOrgIds;
        }

        if (!empty($targetOrgIds)) {

            $targetOrgIds = OrganizationMapping::where('ancestor_id', $userOrgId)
                ->whereIn('descendant_id', $targetOrgIds)
                ->pluck('descendant_id')
                ->all();

            if (empty($targetOrgIds)) return false;

        } else {
            $targetOrgIds[] = $userOrgId;
        }

        if ($userOrgId && !empty($targetOrgIds)) {
            $targetOrgIds = OrganizationMapping::where('ancestor_id', $userOrgId)
                ->whereIn('descendant_id', $targetOrgIds)
                ->pluck('descendant_id')
                ->all();
        }

        $tableMaps = with(new OrganizationMapping)->getTable();
        $tableOrgs = with(new Organization)->getTable();
        $query = DB::table($tableMaps)
            ->join($tableOrgs, "$tableOrgs.organization_id", '=', "$tableMaps.descendant_id")
            ->select("$tableMaps.descendant_id", "$tableMaps.depth")
            ->where("$tableMaps.is_active", true)
            ->where("$tableOrgs.is_active", true)
            ->whereIn("$tableMaps.ancestor_id", $targetOrgIds)
            ->orderByDesc("$tableMaps.depth")
            ->orderBy("$tableMaps.descendant_id");

        $orgIds = [];
        $maxDepth = 0;
        foreach ($query->cursor() as $row) {
            if ($row->depth > $maxDepth) {
                $maxDepth = $row->depth;
                $orgIds = [$row->descendant_id];
            } elseif ($row->depth === $maxDepth) {
                $orgIds[] = $row->descendant_id;
            }
        }

        return empty($orgIds) ? false : $orgIds;
    }
}