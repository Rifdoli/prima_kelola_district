<?php

namespace App\Models;

use App\Traits\Model\TracksUserActivity;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Table(key: 'assessment_question_id')]
#[Fillable([
    'domain', 'weight_domain', 'references', 'practice_area', 'weight_practice_area',
    'scope', 'perangkat', 'question',
    'criteria_a', 'criteria_b', 'criteria_c', 'criteria_d', 'criteria_e',
    'max_score', 'sort_order',
])]
#[Appends(['is_archived'])]
class AssessmentQuestion extends Model
{
    use SoftDeletes, TracksUserActivity;

    public function answers(): HasMany
    {
        return $this->hasMany(SelfAssessmentAnswer::class, 'assessment_question_id', 'assessment_question_id');
    }

    protected function isArchived(): Attribute
    {
        return Attribute::get(fn () => $this->trashed());
    }
}
