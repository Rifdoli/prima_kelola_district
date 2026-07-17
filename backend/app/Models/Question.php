<?php

namespace App\Models;

use App\Traits\Model\TracksUserActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'practice_area_id',
    'question',
    'scope',
    'references',
    'perangkat',
    'max_score',
    'sort_order',
])]
class Question extends Model
{
    use SoftDeletes, TracksUserActivity;

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class, 'practice_area_id')
            ->where('type', 'practice_area');
    }

    public function criterias(): HasMany
    {
        return $this->hasMany(QuestionCriteria::class)->chaperone();
    }
}