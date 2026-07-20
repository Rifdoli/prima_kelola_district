<?php

namespace App\Http\Requests\SelfAssessment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->sname === 'admin_dis';
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:1024', 'mimes:pdf,jpg,jpeg,png,webp'],
            // 'replace_path' => ['nullable', 'string'],
        ];
    }
}
