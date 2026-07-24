<?php

namespace App\Http\Requests\OnSiteAssesment;

use App\Http\Requests\SelfAssessment\StoreAssessmentEvidenceRequest as StoreSelfAssessmentEvidenceRequest;

class StoreAssessmentEvidenceRequest extends StoreSelfAssessmentEvidenceRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()?->role?->sname, ['admin_are', 'admin_nas'], true);
    }
}