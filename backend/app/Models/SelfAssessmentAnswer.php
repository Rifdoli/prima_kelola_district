<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['self_assessment_id', 'assessment_question_id', 'achieved_level', 'evidence_note', 'evidence_file'])]
class SelfAssessmentAnswer extends Model
{
    protected $primaryKey = 'self_assessment_answer_id';

    protected $appends = ['evidence_file_url'];

    protected function evidenceFileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->evidence_file
            ? Storage::disk('public')->url($this->evidence_file)
            : null);
    }

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class, 'self_assessment_id', 'self_assessment_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id', 'assessment_question_id');
    }
}
