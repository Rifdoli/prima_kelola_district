<?php

namespace App\Http\Requests\SelfAssessment;

use Illuminate\Foundation\Http\FormRequest;

class DestroyAssessmentEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_dis';
    }

    public function rules(): array
    {
        return [
            'path' => ['required', 'string'],
        ];
    }
}
