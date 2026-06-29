<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'domain', 'weight_domain', 'references', 'practice_area', 'weight_practice_area',
    'scope', 'perangkat', 'question',
    'criteria_a', 'criteria_b', 'criteria_c', 'criteria_d', 'criteria_e',
    'max_score', 'sort_order',
])]
class AssessmentQuestion extends Model
{
    protected $primaryKey = 'assessment_question_id';

    public function answers(): HasMany
    {
        return $this->hasMany(SelfAssessmentAnswer::class, 'assessment_question_id', 'assessment_question_id');
    }
}
