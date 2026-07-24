<?php

namespace App\Http\Requests\OnDeskAssessment;

use App\Http\Requests\SelfAssessment\GetAssessmentRequest as GetSelfAssessmentRequest;

class GetAssessmentRequest extends GetSelfAssessmentRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_reg';
    }
}