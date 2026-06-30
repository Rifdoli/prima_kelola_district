<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['self_assessment_id', 'assessment_question_id', 'achieved_levels', 'evidence_note', 'evidence_file'])]
class SelfAssessmentAnswer extends Model
{
    protected $primaryKey = 'self_assessment_answer_id';

    protected $appends = ['evidence_file_url', 'score'];

    protected function casts(): array
    {
        return [
            'achieved_levels' => 'array',
        ];
    }

    protected function evidenceFileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->evidence_file
            ? Storage::disk('public')->url($this->evidence_file)
            : null);
    }

    /**
     * Skor pertanyaan ini = jumlah kriteria A-E yang dicentang (0-5).
     */
    protected function score(): Attribute
    {
        return Attribute::get(fn () => count($this->achieved_levels ?? []));
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
