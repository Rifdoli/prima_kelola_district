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
            'criterias.*' => ['required', 'array:title'],
            'criterias.*.title' => ['required', 'string'],
        ];
    }
}