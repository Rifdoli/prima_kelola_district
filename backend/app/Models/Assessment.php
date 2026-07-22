<?php

namespace App\Models;

use App\Traits\Model\TracksUserActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Support\AssessmentScore;

#[Fillable([
    'organization_id',
    'period',
    'type',
    'status',
    'is_active',
    'total_score',
    'prev_assessment_id',
])]
#[Appends(['category'])]
class Assessment extends Model
{
    use TracksUserActivity;

    public const TYPE_SA = 'SA'; // Self Assessment
    public const TYPE_ODA = 'ODA'; // On Desk Assessment
    public const TYPE_OSA = 'OSA'; // On Site Assessment

    public const STATUS_OPEN = 'open';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';

    protected static function booted(): void
    {
        static::updating(function ($model) {
            $model->timestamps = false;
        });

        static::updated(function ($model) {
            $model->timestamps = true;
        });
    }

    public function setUpdatedAt($value) {}

    public function getUpdatedAtColumn()
    {
        return '';
    }

    protected function updatedByCol(): ?string
    {
        return null;
    }

    protected function category(): Attribute
    {
    return Attribute::get(fn () => AssessmentScore::category(
        $this->total_score !== null ? (float) $this->total_score : null
    ));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class)->chaperone();
    }
}