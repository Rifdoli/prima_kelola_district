<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['self_assessment_id', 'assessment_question_id', 'achieved_levels', 'evidence_files'])]
class SelfAssessmentAnswer extends Model
{
    protected $primaryKey = 'self_assessment_answer_id';

    protected $appends = ['evidence_file_urls', 'score'];

    protected function casts(): array
    {
        return [
            'achieved_levels' => 'array',
            'evidence_files' => 'array',
        ];
    }

    /**
     * Map level -> URL publik, dari evidence_files (path per kriteria).
     */
    protected function evidenceFileUrls(): Attribute
    {
        return Attribute::get(fn () => collect($this->evidence_files ?? [])
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->all());
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
        // withTrashed: jawaban historis tetap menemukan pertanyaannya walau sudah diarsip,
        // supaya show()/submit() tidak dapat null saat pertanyaan di-soft-delete.
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id', 'assessment_question_id')
            ->withTrashed();
    }
}
