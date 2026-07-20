<?php

namespace App\Http\Requests\SelfAssessment;

use Illuminate\Foundation\Http\FormRequest;

class GetAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_dis';
    }

    public function rules(): array
    {
        return [
            'domain_ids' => ['nullable', 'array'],
            'domain_ids.*' => ['integer', 'min:1'],
            'practice_area_ids' => ['nullable', 'array'],
            'practice_area_ids.*' => ['integer', 'min:1'],
            'question_ids' => ['nullable', 'array'],
            'question_ids.*' => ['integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['domain_ids', 'practice_area_ids', 'question_ids'] as $key) {
            if ($this->filled($key)) {
                $values = array_values(
                    array_filter(
                        explode(',', $this->query($key)),
                        fn ($value) => $value !== ''
                    )
                );

                $this->merge([
                    $key => array_map('intval', $values),
                ]);
            }
        }
    }
}