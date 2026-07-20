<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class AssessmentPeriod implements ValidationRule
{
    public static function rules(): array
    {
        return ['string', new self()];
    }

    public static function requiredRules(): array
    {
        return ['required', 'string', new self()];
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-Q[1-4]$/', $value)) {
            $fail('The :attribute format must be YYYY-Q1 to YYYY-Q4.');
            return;
        }

        [$year, $quarter] = explode('-Q', $value);

        if ((int) $year < 2020 || (int) $year > (int) now()->year) {
            $fail('The year in :attribute is not valid.');
        }
    }
}
