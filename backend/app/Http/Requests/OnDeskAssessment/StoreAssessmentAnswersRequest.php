<?php

namespace App\Http\Requests\OnDeskAssessment;

use App\Http\Requests\SelfAssessment\StoreAssessmentAnswersRequest as StoreSelfAssessmentAnswersRequest;

class StoreAssessmentAnswersRequest extends StoreSelfAssessmentAnswersRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_reg';
    }
}