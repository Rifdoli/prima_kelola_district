<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Kriteria rubrik yang dijawab. withTrashed: kriteria yang sudah diarsipkan
     * harus tetap terbaca dari sisi jawaban historis.
     */
    public function questionCriteria(): BelongsTo
    {
        return $this->belongsTo(QuestionCriteria::class, 'question_criteria_id')->withTrashed();
    }
}