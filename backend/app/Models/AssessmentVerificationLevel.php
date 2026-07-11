<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

#[Fillable(['assessment_verification_id', 'assessment_question_id', 'level', 'is_valid', 'note', 'evidence_file'])]
class AssessmentVerificationLevel extends Model
{
    protected $primaryKey = 'assessment_verification_level_id';

    protected $appends = ['evidence_file_url'];

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            if (Auth::check()) {
                $row->created_by ??= Auth::id();
                $row->updated_by ??= Auth::id();
            }
        });

        static::updating(function (self $row) {
            if (Auth::check()) {
                $row->updated_by = Auth::id();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean',
        ];
    }

    protected function evidenceFileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->evidence_file
            ? Storage::disk('public')->url($this->evidence_file)
            : null);
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(AssessmentVerification::class, 'assessment_verification_id', 'assessment_verification_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id', 'assessment_question_id');
    }
}
