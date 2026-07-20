<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
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
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}