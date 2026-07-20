<?php

namespace App\Http\Requests\SelfAssessment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentAnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_dis';
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.criteria_id' => ['required', 'integer', 'min:1'],
            'answers.*.value' => ['required', 'boolean'],
            'answers.*.evidence_path' => ['nullable', 'string'],
            'answers.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
