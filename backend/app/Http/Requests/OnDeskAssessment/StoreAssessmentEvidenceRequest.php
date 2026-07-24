<?php

namespace App\Http\Requests\OnDeskAssessment;

use App\Http\Requests\SelfAssessment\StoreAssessmentEvidenceRequest as StoreSelfAssessmentEvidenceRequest;

class StoreAssessmentEvidenceRequest extends StoreSelfAssessmentEvidenceRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_reg';
    }
}