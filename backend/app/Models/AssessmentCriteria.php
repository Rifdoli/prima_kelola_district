<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[WithoutTimestamps]
#[Fillable([
    'assessment_answer_id',
    'question_criteria_id',
    'value',
    'evidence_path',
    'note',
])]
class AssessmentCriteria extends Model
{
    public function assessmentAnswer(): BelongsTo
    {
        return $this->belongsTo(AssessmentAnswer::class);
    }

    public function questionCriteria(): HasOne
    {
        return $this->hasOne(QuestionCriteria::class);
    }
}