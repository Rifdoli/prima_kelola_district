<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

#[Fillable(['self_assessment_id', 'parent_verification_id', 'type', 'status', 'verified_by', 'submitted_at', 'total_score'])]
class AssessmentVerification extends Model
{
    protected $primaryKey = 'assessment_verification_id';

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
            'submitted_at' => 'datetime',
            'total_score' => 'decimal:2',
        ];
    }

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class, 'self_assessment_id', 'self_assessment_id');
    }

    /**
     * Verifikasi induk (OSA -> ODA). Null untuk ODA.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_verification_id', 'assessment_verification_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(AssessmentVerificationLevel::class, 'assessment_verification_id', 'assessment_verification_id');
    }
}
