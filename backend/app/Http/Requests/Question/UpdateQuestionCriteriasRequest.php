<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionCriteriasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'criterias' => ['required', 'array', 'min:1'],
            'criterias.*' => ['required', 'array'],
            'criterias.*.id' => ['nullable', 'integer', 'min:1'],
            'criterias.*.title' => ['required', 'string'],
            'criterias.*.reference' => ['nullable', 'string'],
            'criterias.*.evidence_hint' => ['nullable', 'string'],
        ];
    }
}