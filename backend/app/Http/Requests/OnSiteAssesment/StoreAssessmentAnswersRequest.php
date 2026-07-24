<?php

namespace App\Http\Requests\OnSiteAssesment;

use App\Http\Requests\SelfAssessment\StoreAssessmentAnswersRequest as StoreSelfAssessmentAnswersRequest;

class StoreAssessmentAnswersRequest extends StoreSelfAssessmentAnswersRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()?->role?->sname, ['admin_are', 'admin_nas'], true);
    }
}