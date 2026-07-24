<?php

namespace App\Http\Requests\OnSiteAssesment;

use App\Http\Requests\SelfAssessment\GetAssessmentRequest as GetSelfAssessmentRequest;

class GetAssessmentRequest extends GetSelfAssessmentRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()?->role?->sname, ['admin_are', 'admin_nas'], true);
    }
}