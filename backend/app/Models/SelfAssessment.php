<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

#[Fillable(['organization_id', 'period', 'status', 'submitted_by', 'submitted_at', 'total_score'])]
class SelfAssessment extends Model
{
    protected $primaryKey = 'self_assessment_id';

    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (SelfAssessment $assessment) {
            if (Auth::check()) {
                $assessment->created_by ??= Auth::id();
                $assessment->updated_by ??= Auth::id();
            }
        });

        static::updating(function (SelfAssessment $assessment) {
            if (Auth::check()) {
                $assessment->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'total_score' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by', 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SelfAssessmentAnswer::class, 'self_assessment_id', 'self_assessment_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(AssessmentVerification::class, 'self_assessment_id', 'self_assessment_id');
    }
}
