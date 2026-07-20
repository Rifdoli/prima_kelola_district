<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(timestamps: false)]
#[Fillable(['question_id', 'code', 'sort_order', 'title', 'reference', 'evidence_hint'])]
class QuestionCriteria extends Model
{
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}