<?php

namespace App\Http\Requests\AssessmentTracking;

use App\Rules\AssessmentPeriod;
use Illuminate\Foundation\Http\FormRequest;

class GetAssessmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periods' => ['required', 'array'],
            'periods.*' => AssessmentPeriod::rules(),

            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('periods')) {
            $this->merge([
                'periods' => array_values(
                    array_filter(
                        explode(',', $this->query('periods')),
                        fn ($value) => $value !== ''
                    )
                ),
            ]);
        }

        if ($this->filled('organization_ids')) {
            $values = array_values(
                array_filter(
                    explode(',', $this->query('organization_ids')),
                    fn ($value) => $value !== ''
                )
            );

            $this->merge([
                'organization_ids' => array_map('intval', $values),
            ]);
        }
    }
}