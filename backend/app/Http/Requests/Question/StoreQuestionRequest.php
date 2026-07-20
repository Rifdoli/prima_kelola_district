<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'practice_area_id' => [
                'required',
                'integer',
                Rule::exists('question_groups', 'id')
                    ->where('type', 'practice_area'),
            ],
            'question' => ['required', 'string'],
            'scope' => ['nullable', 'string', 'max:255'],
            'perangkat' => ['nullable', 'string', 'max:255'],
            'criterias.*' => ['required', 'array'],
            'criterias.*.code' => ['required', 'string', 'max:8'],
            'criterias.*.title' => ['required', 'string'],
            'criterias.*.reference' => ['nullable', 'string'],
            'criterias.*.evidence_hint' => ['nullable', 'string'],
        ];
    }
}