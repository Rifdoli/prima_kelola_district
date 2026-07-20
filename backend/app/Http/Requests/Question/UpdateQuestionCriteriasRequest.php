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
            'criterias.*' => ['required', 'array'],
            'criterias.*.id' => ['nullable', 'integer', 'min:1'],
            'criterias.*.title' => ['required', 'string', 'max:255'],
        ];
    }
}